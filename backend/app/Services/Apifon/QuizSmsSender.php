<?php

namespace App\Services\Apifon;

use App\Models\Lead;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Orchestrates sending the redemption-code SMS for a lead and persists
 * the full request/response payload in `sms_logs`.
 *
 * Honours `quiz.apifon.enabled` — when false, no HTTP call is made and
 * a `dry_run` row is stored instead (useful in dev/staging).
 */
class QuizSmsSender
{
    public function __construct(
        protected ApifonClient $client,
    ) {
    }

    public function sendForLead(Lead $lead): SmsLog
    {
        $text = $this->renderTemplate($lead);
        $phone = $this->normalisePhone($lead->phone);
        $senderId = (string) config('quiz.apifon.sender_id');

        // Dry-run mode: log everything but do not hit the API.
        if (! (bool) config('quiz.apifon.enabled', false)) {
            return SmsLog::create([
                'lead_id'          => $lead->id,
                'provider'         => 'apifon',
                'phone'            => $phone,
                'sender_id'        => $senderId,
                'request_payload'  => [
                    'note'    => 'APIFON_ENABLED=false — message NOT actually sent.',
                    'message' => ['text' => $text, 'sender_id' => $senderId],
                    'subscribers' => [['number' => $phone]],
                ],
                'response_payload' => null,
                'http_status'      => null,
                'duration_ms'      => 0,
                'status'           => SmsLog::STATUS_DRY_RUN,
                'sent_at'          => now(),
            ]);
        }

        try {
            $result = $this->client->sendSms($phone, $text);
        } catch (Throwable $e) {
            Log::error('Apifon SMS dispatch threw', ['exception' => $e, 'lead_id' => $lead->id]);

            return SmsLog::create([
                'lead_id'         => $lead->id,
                'provider'        => 'apifon',
                'phone'           => $phone,
                'sender_id'       => $senderId,
                'request_payload' => ['text' => $text],
                'status'          => SmsLog::STATUS_FAILED,
                'error_message'   => mb_substr($e->getMessage(), 0, 500),
                'sent_at'         => now(),
            ]);
        }

        return SmsLog::create([
            'lead_id'          => $lead->id,
            'provider'         => 'apifon',
            'phone'            => $phone,
            'sender_id'        => $senderId,
            'request_payload'  => $result->requestPayload,
            'response_payload' => $result->responsePayload,
            'http_status'      => $result->httpStatus,
            'duration_ms'      => $result->durationMs,
            'status'           => $result->success ? SmsLog::STATUS_SENT : SmsLog::STATUS_FAILED,
            'error_message'    => $result->errorMessage ? mb_substr($result->errorMessage, 0, 500) : null,
            'sent_at'          => now(),
        ]);
    }

    protected function renderTemplate(Lead $lead): string
    {
        $template = (string) config('quiz.apifon.sms_template');
        $url      = (string) config('quiz.redemption.url', '');

        return strtr($template, [
            '{code}' => (string) $lead->redemption_code,
            '{url}'  => $url,
            '{name}' => $lead->name,
        ]);
    }

    /**
     * Convert a stored phone like "6912345678" → "306912345678".
     */
    protected function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';

        // Already prefixed with country code? leave as-is.
        $countryCode = (string) config('quiz.apifon.country_code', '30');
        if (str_starts_with($digits, $countryCode)) {
            return $digits;
        }

        return $countryCode . $digits;
    }
}
