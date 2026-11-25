<?php

namespace App\Http\Requests;

use App\Enums\QuestionType;
use App\Models\Document;
use App\Models\DocumentQuestion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A filled-in medical document: one answer per question of the document, keyed by question id.
 * Choice answers must be one of the options, so the score is never computed from a typo.
 */
class DocumentResponseRequest extends FormRequest
{
    /** @var Collection<int, DocumentQuestion>|null */
    private ?Collection $questions = null;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $questions = $this->questions();

        return [
            'document_id' => ['required', 'integer', 'exists:documents,id'],
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            // Only questions of this document may be answered.
            'answers' => ['required', 'array:'.implode(',', $questions->modelKeys())],
            'previous_url' => ['required', 'string'],
            ...$questions->mapWithKeys(fn (DocumentQuestion $question): array => [
                "answers.{$question->id}" => $this->answerRules($question),
            ])->all(),
        ];
    }

    /**
     * @return array{patient_id: int, document_id: int, answers: array<int|string, string>}
     */
    public function documentResponse(): array
    {
        return [
            'patient_id' => $this->integer('patient_id'),
            'document_id' => $this->integer('document_id'),
            'answers' => array_map(strval(...), $this->array('answers')),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'patient_id' => 'пациент',
            'document_id' => 'документ',
            'answers' => 'ответы',
            ...$this->questions()->mapWithKeys(fn (DocumentQuestion $question): array => [
                "answers.{$question->id}" => '«'.$question->question.'»',
            ])->all(),
        ];
    }

    /**
     * @return list<mixed>
     */
    private function answerRules(DocumentQuestion $question): array
    {
        return match ($question->type) {
            QuestionType::Choice => ['required', 'string', Rule::in($question->options ?? [])],
            QuestionType::Boolean => ['required', Rule::in(['0', '1'])],
            QuestionType::Text => ['required', 'string', 'max:1000'],
        };
    }

    /**
     * @return Collection<int, DocumentQuestion>
     */
    private function questions(): Collection
    {
        if ($this->questions !== null) {
            return $this->questions;
        }

        $document = is_numeric($this->input('document_id'))
            ? Document::query()->with('questions')->find((int) $this->input('document_id'))
            : null;

        return $this->questions = $document === null ? new Collection : $document->questions;
    }
}
