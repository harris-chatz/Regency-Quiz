<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\QuestionOption;
use App\Models\SmsLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadsAdminController extends Controller
{
    public function index()
    {
        $totalLeads = Lead::count();
        $totalToday = Lead::whereDate('created_at', today())->count();

        $byPersona = Lead::query()
            ->selectRaw('persona_color, COUNT(*) as count')
            ->groupBy('persona_color')
            ->pluck('count', 'persona_color')
            ->all();

        $smsStats = SmsLog::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $latestLeads = Lead::query()
            ->with('latestSmsLog')
            ->latest('id')
            ->limit(50)
            ->get();

        return view('admin.leads', [
            'totalLeads'   => $totalLeads,
            'totalToday'   => $totalToday,
            'byPersona'    => $byPersona,
            'smsStats'     => $smsStats,
            'latestLeads'  => $latestLeads,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filename = sprintf('regency-quiz-leads_%s.csv', now()->format('Y-m-d_His'));

        // Build a map of option_id => label so we can hydrate the per-answer
        // labels without N+1 queries during the streaming loop.
        $optionLabels = QuestionOption::query()
            ->pluck('label', 'id')
            ->all();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'X-Accel-Buffering'   => 'no',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
            'Pragma'              => 'no-cache',
        ];

        return response()->stream(function () use ($optionLabels) {
            $out = fopen('php://output', 'w');

            // Excel reads UTF-8 reliably when the file starts with a BOM,
            // otherwise Greek shows up as garbage.
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'ID',
                'Ημερομηνία',
                'Ονοματεπώνυμο',
                'Email',
                'Τηλέφωνο',
                'Persona',
                'Έχει επισκεφθεί',
                'Age Consent',
                'Terms Consent',
                'Marketing Consent',
                'Κωδικός Εξαργύρωσης',
                'Q1 απάντηση', 'Q1 χρώμα',
                'Q2 απάντηση', 'Q2 χρώμα',
                'Q3 απάντηση', 'Q3 χρώμα',
                'Quiz έναρξη',
                'SMS Status',
                'SMS sent_at',
                'SMS error',
                'IP',
                'User Agent',
            ]);

            Lead::query()
                ->with('latestSmsLog')
                ->orderBy('id')
                ->chunk(500, function ($leads) use ($out, $optionLabels) {
                    foreach ($leads as $lead) {
                        $answersByOrder = collect($lead->answers ?? [])
                            ->keyBy(fn ($a) => (int) ($a['question_id'] ?? 0));

                        $row = [
                            $lead->id,
                            optional($lead->created_at)->format('Y-m-d H:i:s'),
                            $lead->name,
                            $lead->email,
                            $lead->phone,
                            $lead->persona_color,
                            $this->yesNo($lead->has_visited_casino),
                            $this->yesNo($lead->age_consent),
                            $this->yesNo($lead->terms_consent),
                            $this->yesNo($lead->marketing_consent),
                            $lead->redemption_code,
                        ];

                        // Q1, Q2, Q3 — label + color
                        for ($q = 1; $q <= 3; $q++) {
                            $ans = $answersByOrder->first(
                                fn ($a) => (int) ($a['question_id'] ?? 0) === $q,
                            );
                            $optionId = $ans['option_id'] ?? null;
                            $row[] = $optionId !== null
                                ? ($optionLabels[$optionId] ?? "option#{$optionId}")
                                : '';
                            $row[] = $ans['color'] ?? '';
                        }

                        $row[] = optional($lead->quiz_started_at)->format('Y-m-d H:i:s');

                        $smsLog = $lead->latestSmsLog;
                        $row[] = $smsLog?->status ?? '';
                        $row[] = $smsLog?->sent_at?->format('Y-m-d H:i:s') ?? '';
                        $row[] = $smsLog?->error_message ?? '';

                        $row[] = $lead->ip_address;
                        $row[] = $lead->user_agent;

                        fputcsv($out, $row);
                    }

                    if (function_exists('ob_flush')) {
                        @ob_flush();
                    }
                    flush();
                });

            fclose($out);
        }, 200, $headers);
    }

    protected function yesNo(?bool $value): string
    {
        if ($value === null) {
            return '';
        }

        return $value ? 'NAI' : 'OXI';
    }
}
