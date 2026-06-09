<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            [
                'order' => 1,
                'text' => 'Ποιό είναι το αγαπημένο σου χρώμα;',
                'options' => [
                    ['label' => 'Κίτρινο', 'color' => 'yellow'],
                    ['label' => 'Πράσινο', 'color' => 'green'],
                    ['label' => 'Ροζ',     'color' => 'pink'],
                ],
            ],
            [
                'order' => 2,
                'text' => 'Τι προτιμάς;',
                'options' => [
                    ['label' => 'DJ Party',         'color' => 'green'],
                    ['label' => 'Live Dance Show',  'color' => 'yellow'],
                    ['label' => 'Live Band',        'color' => 'pink'],
                ],
            ],
            [
                'order' => 3,
                'text' => 'Τι θα προτιμούσες σε μια επίσκεψη στο Regency Casino Mont Parnes;',
                'options' => [
                    ['label' => 'Γεύμα με θέα στα 1055 μέτρα', 'color' => 'green'],
                    ['label' => 'Παιχνίδι γεμάτο αδρεναλίνη',  'color' => 'yellow'],
                    ['label' => 'Βόλτα με Τελεφερίκ',          'color' => 'pink'],
                ],
            ],
        ];

        foreach ($questions as $data) {
            $question = Question::updateOrCreate(
                ['order' => $data['order']],
                ['text' => $data['text'], 'is_active' => true],
            );

            $question->options()->delete();

            foreach ($data['options'] as $index => $opt) {
                $question->options()->create([
                    'label' => $opt['label'],
                    'color' => $opt['color'],
                    'order' => $index + 1,
                ]);
            }
        }
    }
}
