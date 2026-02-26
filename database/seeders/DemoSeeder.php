<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\Question;
use App\Models\Option;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Subject
        $subject = Subject::create([
            'name' => 'Matematika (Demo)',
            'description' => 'Matematika fanidan sinov testi',
        ]);

        // 2. Create Quiz
        $quiz = Quiz::create([
            'subject_id' => $subject->id,
            'title' => 'Matematika - Asosiy Imtihon',
            'access_code' => 'MAT-2024',
            'time_limit' => 30, // 30 minutes
            'pass_score' => 70,
            'is_random' => true,
        ]);

        // 3. Create Questions & Options
        $questions = [
            [
                'text' => '2 + 2 * 2 nechaga teng?',
                'type' => 'single',
                'options' => [
                    ['text' => '4', 'is_correct' => false],
                    ['text' => '6', 'is_correct' => true],
                    ['text' => '8', 'is_correct' => false],
                    ['text' => '10', 'is_correct' => false],
                ]
            ],
            [
                'text' => 'Uchburchakning ichki burchaklari yig\'indisi necha gradus?',
                'type' => 'single',
                'options' => [
                    ['text' => '90', 'is_correct' => false],
                    ['text' => '180', 'is_correct' => true],
                    ['text' => '270', 'is_correct' => false],
                    ['text' => '360', 'is_correct' => false],
                ]
            ],
            [
                'text' => '5 ning kvadrati necha?',
                'type' => 'single',
                'options' => [
                    ['text' => '10', 'is_correct' => false],
                    ['text' => '20', 'is_correct' => false],
                    ['text' => '25', 'is_correct' => true],
                    ['text' => '50', 'is_correct' => false],
                ]
            ],
        ];

        foreach ($questions as $qData) {
            $question = Question::create([
                'subject_id' => $subject->id,
                'text' => $qData['text'],
                'type' => $qData['type'],
            ]);

            foreach ($qData['options'] as $oData) {
                Option::create([
                    'question_id' => $question->id,
                    'text' => $oData['text'],
                    'is_correct' => $oData['is_correct'],
                ]);
            }

            // Attach question to quiz
            $quiz->questions()->attach($question->id);
        }
    }
}
