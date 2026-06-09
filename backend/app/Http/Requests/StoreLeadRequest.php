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
            'name'  => ['required', 'string', 'min:3', 'max:150'],
            'email' => [
                'required',
                'email:rfc,filter',
                'regex:/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}$/',
                'max:150',
            ],
            'phone' => ['required', 'string', 'regex:/^69\d{8}$/'],

            'age_consent'        => ['required', 'accepted'],
            'terms_consent'      => ['required', 'accepted'],
            'marketing_consent'  => ['required', 'accepted'],

            'persona_color'      => ['required', Rule::in(['green', 'yellow', 'pink'])],
            'has_visited_casino' => ['nullable', 'boolean'],
            'started_at'         => ['nullable', 'date'],

            'answers'                => ['nullable', 'array', 'max:10'],
            'answers.*.question_id'  => ['required_with:answers', 'integer', 'exists:questions,id'],
            'answers.*.option_id'    => ['required_with:answers', 'integer', 'exists:question_options,id'],
            'answers.*.color'        => ['required_with:answers', Rule::in(['green', 'yellow', 'pink'])],
            'answers.*.answered_at'  => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'Το ονοματεπώνυμο είναι υποχρεωτικό.',
            'name.min'        => 'Συμπλήρωσε το πλήρες ονοματεπώνυμό σου.',
            'email.required'  => 'Το email είναι υποχρεωτικό.',
            'email.email'     => 'Δεν είναι έγκυρη διεύθυνση email.',
            'email.regex'     => 'Δεν είναι έγκυρη διεύθυνση email.',
            'phone.required'  => 'Το τηλέφωνο είναι υποχρεωτικό.',
            'phone.regex'     => 'Πρέπει να ξεκινάει με 69 και να έχει 10 ψηφία.',

            'age_consent.accepted'       => 'Πρέπει να επιβεβαιώσεις την ηλικία σου.',
            'terms_consent.accepted'     => 'Πρέπει να αποδεχτείς τους Όρους Χρήσης.',
            'marketing_consent.accepted' => 'Πρέπει να αποδεχτείς να λαμβάνεις marketing επικοινωνία.',

            'persona_color.in' => 'Μη έγκυρο persona color.',
        ];
    }
}
