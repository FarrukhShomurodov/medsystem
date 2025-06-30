<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentResponse;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;

class DocumentResponseService
{
    /**
     * @param array<string, mixed> $validated
     */
    public function store(array $validated): DocumentResponse
    {
        $documentResponse = DB::transaction(function () use ($validated) {
            $map = Patient::find($validated['patient_id'])->examinationMaps()->where('is_active', true);

            if ($map->exists()) {
                $validated['examination_map_id'] = $map->first()->id;
            }

            $document = Document::find($validated['document_id']);
            $totalScore = 0;

            if ($document->title === 'Лист оценки риска падения детей по шкале "Хампти-Дампти"') {
                $totalScore = $this->calculateHumptyDumptyScore($validated['answers']);
            } elseif ($document->title === 'Лист оценки риска падения по шкале Морзе') {
                $totalScore = $this->calculateMorseScore($validated['answers']);
            } elseif ($document->title === 'Лист оценки риска падения амбулаторного пациента (КДО)') {
                $totalScore = $this->calculateOutpatientScore($validated['answers']);
            } elseif ($document->title === 'Лист оценки боли (для взрослых и детей от 3 лет)') {
                $totalScore = $this->calculatePainScore($validated['answers']);
            } elseif ($document->title === 'Лист оценки боли FLACC (дети 1-3 года)') {
                $totalScore = $this->calculateFlaccScore($validated['answers']);
            } elseif ($document->title === 'Лист оценки боли NIPS (новорожденные и дети до года)') {
                $totalScore = $this->calculateNipsScore($validated['answers']);
            } elseif ($document->title === 'Лист оценки боли CPOT (невербальные пациенты)') {
                $totalScore = $this->calculateCpotScore($validated['answers']);
            }


            // Добавить другие документы при необходимости

            $validated['total_score'] = $totalScore;

            $documentResponse = DocumentResponse::query()->create($validated);

            foreach ($validated['answers'] as $index => $answer) {
                $documentResponse->answers()->create([
                    'question_id' => $index,
                    'answer' => $answer,
                ]);
            }

            return $documentResponse;
        });

        return $documentResponse;
    }

    private function calculateMorseScore(array $answers): int
    {
        $scores = [
            // Вопрос 1: Падения (0 или 25)
            0 => fn($ans) => $ans === 'Да' ? 25 : 0,

            // Вопрос 2: Заболевания (0 или 15)
            1 => fn($ans) => $ans === 'Да' ? 15 : 0,

            // Вопрос 3: Способ передвижения (0, 15 или 30)
            2 => function ($ans) {
                if ($ans === 'Костыли / ходунки / трость') {
                    return 15;
                }
                if ($ans === 'Опора о мебель или стены') {
                    return 30;
                }
                return 0; // Сам/постельный режим
            },

            // Вопрос 4: Внутривенное вливание (0 или 20)
            3 => fn($ans) => $ans === 'Да' ? 20 : 0,

            // Вопрос 5: Походка (0, 10 или 20)
            4 => function ($ans) {
                if ($ans === 'Слегка несвободная (ходит с остановками, шаги короткие, иногда с задержкой)') {
                    return 10;
                }
                if ($ans === 'Нарушения (не может встать, ходит опираясь, смотрит вниз)') {
                    return 20;
                }
                return 0; // Нормальная
            },

            // Вопрос 6: Психическое состояние (0 или 15)
            5 => fn($ans) => $ans === 'Не знает или забывает, что нужна помощь при движении' ? 15 : 0
        ];

        $total = 0;

        foreach (array_values($answers) as $index => $answer) {
            if (isset($scores[$index])) {
                $total += $scores[$index]($answer);
            }
        }


        return $total;
    }

    private function calculateHumptyDumptyScore(array $answers): int
    {
        $scores = [
            // Возраст
            0 => function ($ans) {
                if ($ans === 'Младше 3-х лет') {
                    return 4;
                }
                if ($ans === 'От 3 до 7 лет') {
                    return 3;
                }
                if ($ans === 'От 7 до 13 лет') {
                    return 2;
                }
                return 1; // 13-18 лет
            },

            // Пол
            1 => fn($ans) => $ans === 'Мужской' ? 2 : 1,

            // Диагноз
            2 => function ($ans) {
                if ($ans === 'Неврологические нарушения') {
                    return 4;
                }
                if ($ans === 'Нарушения оксигенации') {
                    return 3;
                }
                if ($ans === 'Психологические/поведенческие расстройства') {
                    return 2;
                }
                return 1; // Другое
            },

            // Когнитивные расстройства
            3 => function ($ans) {
                if ($ans === 'Не осознает ограничений') {
                    return 3;
                }
                if ($ans === 'Забывает об ограничениях') {
                    return 2;
                }
                return 1; // Ориентируется
            },

            // Факторы среды
            4 => function ($ans) {
                if ($ans === 'Падения в анамнезе') {
                    return 4;
                }
                if ($ans === 'Вспомогательные средства') {
                    return 3;
                }
                if ($ans === 'Лежачий / на руках') {
                    return 2;
                }
                return 1; // Ходит самостоятельно
            },

            // Состояние после операции/седации
            5 => function ($ans) {
                if ($ans === 'До 24 ч') {
                    return 3;
                }
                if ($ans === '24-48 ч') {
                    return 2;
                }
                return 1; // Более 48 ч
            },

            // Медикаменты
            6 => function ($ans) {
                if ($ans === '2 и более из указанных групп') {
                    return 3;
                }
                if ($ans === '1 медикамент') {
                    return 2;
                }
                return 1; // Не принимает/другие
            }
        ];

        $total = 0;
        foreach (array_values($answers) as $index => $answer) {
            if (isset($scores[$index])) {
                $total += $scores[$index]($answer);
            }
        }

        return $total;
    }

    private function calculateOutpatientScore(array $answers): int
    {
        $score = 0;
        foreach ($answers as $answer) {
            // Каждый ответ "Да" дает 1 балл
            if ($answer === 'Да') {
                $score++;
            }
        }
        return $score;
    }

    private function calculatePainScore(array $answers): int
    {
        if (empty($answers[0])) {
            return 0;
        }

        $intensityAnswer = $answers[0];

        // Извлекаем числовое значение из ответа
        preg_match('/^(\d+)/', $intensityAnswer, $matches);
        return $matches[1] ?? 0;
    }

    private function calculateFlaccScore(array $answers): int
    {
        $total = 0;
        foreach (array_values($answers) as $answer) {
            if (preg_match('/^(\d+)/', $answer, $matches)) {
                $total += (int)$matches[1];
            }
        }
        return $total;
    }

    private function calculateNipsScore(array $answers): int
    {
        $total = 0;
        foreach (array_values($answers) as $answer) {
            if (preg_match('/^(\d+)/', $answer, $matches)) {
                $total += (int)$matches[1];
            }
        }
        return $total;
    }

    private function calculateCpotScore(array $answers): int
    {
        $total = 0;
        foreach (array_values($answers) as $index => $answer) {
            // Пропускаем вопрос "Интерпретация баллов CPOT"
            if ($index === 5) {
                continue;
            }

            // Извлекаем число из начала строки ответа
            if (preg_match('/^(\d+)/', $answer, $matches)) {
                $total += (int)$matches[1];
            }
        }
        return $total;
    }
}
