<?php

namespace App\Services\Apifon;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Minimal client for Apifon's IM Gateway REST API.
 *
 * Docs: https://docs.apifon.com/
 * Endpoint (default): POST https://ars.apifon.com/services/api/v1/im/send
 *
 * Authentication: Bearer OAuth token in the Authorization header.
 */
class ApifonClient
{
    public function __construct(
        protected string $baseUrl,
        protected string $endpoint,
        protected ?string $oauthToken,
        protected string $senderId,
        protected int $timeout = 10,
    ) {
    }

    public static function fromConfig(): self
    {
        return new self(
            baseUrl: rtrim((string) config('quiz.apifon.base_url'), '/'),
            endpoint: '/' . ltrim((string) config('quiz.apifon.endpoint'), '/'),
            oauthToken: config('quiz.apifon.oauth_token'),
            senderId: (string) config('quiz.apifon.sender_id'),
            timeout: (int) config('quiz.apifon.timeout', 10),
        );
    }

    /**
     * Send a single SMS to a single recipient.
     *
     * @param  string  $phone E.164-style without "+", e.g. "306912345678"
     * @return ApifonResult
     */
    public function sendSms(string $phone, string $text): ApifonResult
    {
        $payload = $this->buildPayload($phone, $text);

        if (empty($this->oauthToken)) {
            return ApifonResult::failure(
                requestPayload: $payload,
                errorMessage: 'APIFON_OAUTH_TOKEN is not configured.',
            );
        }

        $url = $this->baseUrl . $this->endpoint;
        $start = microtime(true);

        try {
            $response = Http::withToken($this->oauthToken)
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

    protected function buildPayload(string $phone, string $text): array
    {
        return [
            'message' => [
                'text'      => $text,
                'sender_id' => $this->senderId,
                // Greek text requires UCS-2 (16-bit) encoding; auto-detect.
                'dc'        => $this->hasNonGsm7Chars($text) ? 2 : 0,
            ],
            'subscribers' => [
                ['number' => $phone],
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
