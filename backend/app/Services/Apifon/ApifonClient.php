<?php

namespace App\Services\Apifon;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Client for Apifon's IM Gateway REST API using OAuth2 client_credentials.
 *
 * Docs: https://docs.apifon.com/
 *
 * Flow:
 *   1. Exchange (client_id, client_secret) at /oauth2/token → Bearer
 *      Bearers expire after 24h, so we cache them with TTL just below
 *      `expires_in` (with a 5-minute safety margin).
 *   2. Use Bearer in POST /services/api/v1/im/send.
 *   3. On 401 (token invalidated/revoked mid-cache), bust cache and retry once.
 */
class ApifonClient
{
    /** Safety margin to avoid using a Bearer that's about to expire. */
    protected const TOKEN_TTL_SAFETY_MARGIN_SECONDS = 300;

    /** Cache key for the Bearer; namespaced by client_id so multiple credentials never collide. */
    protected string $cacheKey;

    public function __construct(
        protected string $baseUrl,
        protected string $endpoint,
        protected string $identityUrl,
        protected ?string $clientId,
        protected ?string $clientSecret,
        protected string $scope,
        protected string $senderId,
        protected int $timeout = 10,
    ) {
        $this->cacheKey = 'apifon:bearer:' . substr(sha1((string) $clientId), 0, 16);
    }

    public static function fromConfig(): self
    {
        return new self(
            baseUrl: rtrim((string) config('quiz.apifon.base_url'), '/'),
            endpoint: '/' . ltrim((string) config('quiz.apifon.endpoint'), '/'),
            identityUrl: (string) config('quiz.apifon.identity_url'),
            clientId: config('quiz.apifon.client_id'),
            clientSecret: config('quiz.apifon.client_secret'),
            scope: (string) config('quiz.apifon.scope', 'imGateway'),
            senderId: (string) config('quiz.apifon.sender_id'),
            timeout: (int) config('quiz.apifon.timeout', 10),
        );
    }

    /**
     * Send a single SMS to a single recipient.
     *
     * @param  string  $phone  E.164-style without "+", e.g. "306912345678"
     * @return ApifonResult
     */
    public function sendSms(string $phone, string $text): ApifonResult
    {
        $payload = $this->buildPayload($phone, $text);

        if (empty($this->clientId) || empty($this->clientSecret)) {
            return ApifonResult::failure(
                requestPayload: $payload,
                errorMessage: 'APIFON_CLIENT_ID or APIFON_CLIENT_SECRET is not configured.',
            );
        }

        // First attempt with cached or freshly-issued Bearer.
        $result = $this->dispatchSms($payload);

        // If Apifon says the token is invalid/expired, bust cache and retry exactly once.
        if (
            $result->httpStatus === 401
            && is_array($result->responsePayload)
            && $this->responseHeaderContainsInvalidToken($result->responsePayload)
        ) {
            Log::warning('Apifon Bearer rejected, refreshing once', ['cache_key' => $this->cacheKey]);
            Cache::forget($this->cacheKey);
            $result = $this->dispatchSms($payload);
        }

        return $result;
    }

    /**
     * Step 1+2: get Bearer (cached) → POST /im/send.
     */
    protected function dispatchSms(array $payload): ApifonResult
    {
        try {
            $bearer = $this->getBearerToken();
        } catch (\Throwable $e) {
            return ApifonResult::failure(
                requestPayload: $payload,
                errorMessage: 'Failed to obtain Bearer token: ' . $e->getMessage(),
            );
        }

        $url = $this->baseUrl . $this->endpoint;
        $start = microtime(true);

        try {
            $response = Http::withToken($bearer)
                ->acceptJson()
                ->asJson()
                ->timeout($this->timeout)
                ->post($url, $payload);

            $durationMs = (int) round((microtime(true) - $start) * 1000);

            return $this->buildResultFromResponse($payload, $response, $durationMs);
        } catch (ConnectionException $e) {
            $durationMs = (int) round((microtime(true) - $start) * 1000);
            return ApifonResult::failure(
                requestPayload: $payload,
                errorMessage: 'Connection error: ' . $e->getMessage(),
                durationMs: $durationMs,
            );
        } catch (RequestException $e) {
            $durationMs = (int) round((microtime(true) - $start) * 1000);
            return ApifonResult::failure(
                requestPayload: $payload,
                errorMessage: 'Request error: ' . $e->getMessage(),
                durationMs: $durationMs,
                httpStatus: $e->response?->status(),
                responsePayload: $this->safeJson($e->response),
            );
        }
    }

    /**
     * Get a Bearer access_token, exchanging client_id+client_secret if no
     * usable cached token exists.
     *
     * @throws \RuntimeException when the identity service returns a non-200 response
     */
    protected function getBearerToken(): string
    {
        $cached = Cache::get($this->cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $response = Http::asForm()
            ->acceptJson()
            ->timeout($this->timeout)
            ->post($this->identityUrl, [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope'         => $this->scope,
            ]);

        if (! $response->successful()) {
            $body = $this->safeJson($response);
            throw new \RuntimeException(sprintf(
                'OAuth token exchange failed (HTTP %d): %s',
                $response->status(),
                $body['error_description'] ?? $body['error'] ?? $response->body(),
            ));
        }

        $data = $response->json();
        $accessToken = $data['access_token'] ?? null;
        $expiresIn   = (int) ($data['expires_in'] ?? 0);

        if (! is_string($accessToken) || $accessToken === '') {
            throw new \RuntimeException('OAuth token response missing access_token.');
        }

        // Cache with TTL slightly below expiry to avoid race with token expiration.
        $ttl = max(60, $expiresIn - self::TOKEN_TTL_SAFETY_MARGIN_SECONDS);
        Cache::put($this->cacheKey, $accessToken, $ttl);

        Log::info('Apifon Bearer issued', [
            'cache_key' => $this->cacheKey,
            'expires_in' => $expiresIn,
            'cached_ttl' => $ttl,
            'scope'      => $data['scope'] ?? null,
        ]);

        return $accessToken;
    }

    protected function buildPayload(string $phone, string $text): array
    {
        // IM Gateway requires `im_channels`. Apifon delivers via Viber when
        // possible and falls back to SMS otherwise.
        return [
            'subscribers' => [
                ['number' => $phone],
            ],
            'im_channels' => [
                [
                    'sender_id' => $this->senderId,
                    'text'      => $text,
                ],
            ],
        ];
    }

    protected function buildResultFromResponse(array $payload, Response $response, int $durationMs): ApifonResult
    {
        $responseBody = $this->safeJson($response);

        // Capture diagnostic headers so we can tell apart invalid_token vs
        // invalid_scope vs IP-restriction failures without re-running tests.
        $diagnosticHeaders = array_intersect_key(
            $response->headers(),
            array_flip(['WWW-Authenticate', 'www-authenticate', 'X-Apifon-Error']),
        );
        $responseBody['_headers'] = $diagnosticHeaders;

        if ($response->successful()) {
            return ApifonResult::success(
                requestPayload: $payload,
                responsePayload: $responseBody,
                httpStatus: $response->status(),
                durationMs: $durationMs,
            );
        }

        $errorMessage = 'Apifon returned HTTP ' . $response->status();
        foreach ($diagnosticHeaders as $values) {
            $errorMessage .= ' — ' . implode(', ', (array) $values);
        }

        return ApifonResult::failure(
            requestPayload: $payload,
            errorMessage: $errorMessage,
            responsePayload: $responseBody,
            httpStatus: $response->status(),
            durationMs: $durationMs,
        );
    }

    protected function responseHeaderContainsInvalidToken(array $responsePayload): bool
    {
        $headers = $responsePayload['_headers'] ?? [];
        foreach ($headers as $values) {
            foreach ((array) $values as $value) {
                if (stripos((string) $value, 'invalid_token') !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function safeJson(?Response $response): array
    {
        if (! $response) {
            return [];
        }
        try {
            return $response->json() ?? ['_raw' => $response->body()];
        } catch (\Throwable) {
            return ['_raw' => $response->body()];
        }
    }

    /**
     * Detect characters outside the basic GSM-7 alphabet (which would
     * require UCS-2 encoding). Cheap approximation: anything outside ASCII
     * triggers UCS-2.
     */
    protected function hasNonGsm7Chars(string $text): bool
    {
        return preg_match('/[^\x00-\x7F]/', $text) === 1;
    }
}
