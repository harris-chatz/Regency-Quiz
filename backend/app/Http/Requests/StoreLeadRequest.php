<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Optional fields — δεν τα ζητάει η νέα φόρμα αλλά τα αφήνουμε
            // εδώ ώστε αν προστεθούν αργότερα να μη χρειαστούν αλλαγές.
            'name'  => ['nullable', 'string', 'max:150'],

            'email' => ['required', 'email:rfc,filter', 'max:150'],
            'phone' => ['required', 'string', 'regex:/^\d{10}$/'],

            // Required consent
            'age_consent'        => ['required', 'accepted'],

            // Optional consents — δεν είναι hard-required στη νέα φόρμα
            'terms_consent'      => ['nullable', 'boolean'],
            'marketing_consent'  => ['nullable', 'boolean'],

            'persona_color'      => ['required', Rule::in(['green', 'yellow', 'pink'])],
            'has_visited_casino' => ['nullable', 'boolean'],
            'started_at'         => ['nullable', 'date'],

            'answers'                => ['nullable', 'array', 'max:10'],
            'answers.*.question_id'  => ['nullable', 'integer'],
            'answers.*.option_id'    => ['nullable', 'integer'],
            'answers.*.color'        => ['nullable', Rule::in(['green', 'yellow', 'pink'])],
            'answers.*.answered_at'  => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'  => 'Το email είναι υποχρεωτικό.',
            'email.email'     => 'Δεν είναι έγκυρη διεύθυνση email.',
            'phone.required'  => 'Το τηλέφωνο είναι υποχρεωτικό.',
            'phone.regex'     => 'Πρέπει να έχει 10 ψηφία.',

            'age_consent.accepted' => 'Πρέπει να επιβεβαιώσεις την ηλικία σου.',

            'persona_color.in' => 'Μη έγκυρο persona color.',
        ];
    }
}
