<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Resources\LeadResource;
use App\Models\Lead;
use App\Services\Apifon\QuizSmsSender;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class LeadController extends Controller
{
    public function __construct(
        protected QuizSmsSender $smsSender,
    ) {
    }

    public function store(StoreLeadRequest $request)
    {
        $data = $request->validated();

        $lead = Lead::create([
            'name'  => $data['name'],
            'email' => mb_strtolower($data['email']),
            'phone' => $data['phone'],

            'age_consent'        => $data['age_consent'],
            'terms_consent'      => $data['terms_consent'],
            'marketing_consent'  => $data['marketing_consent'],

            'persona_color'      => $data['persona_color'],
            'has_visited_casino' => $data['has_visited_casino'] ?? null,
            'quiz_started_at'    => $data['started_at'] ?? null,
            'answers'            => $data['answers'] ?? null,

            'redemption_code' => $this->generateRedemptionCode(),

            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
        ]);

        // SMS dispatch should NEVER bring the user-facing request down;
        // we already have the lead persisted with its redemption code.
        try {
            $this->smsSender->sendForLead($lead);
        } catch (Throwable $e) {
            Log::error('Apifon SMS dispatch failed', [
                'lead_id'   => $lead->id,
                'exception' => $e,
            ]);
        }

        return (new LeadResource($lead))
            ->response()
            ->setStatusCode(201);
    }

    protected function generateRedemptionCode(): string
    {
        $mode = config('quiz.redemption.mode', 'generic');

        if ($mode === 'unique') {
            $prefix = (string) config('quiz.redemption.unique_prefix', 'RCMP-');
            $length = (int) config('quiz.redemption.unique_length', 8);

            return $prefix . Str::upper(Str::random($length));
        }

        return (string) config('quiz.redemption.generic_code', 'RCMP2026');
    }
}
