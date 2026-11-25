{{--
    One question of the survey editor (resources/js/pages/survey-editor.js).
    $prefix: the name prefix of its fields: "questions[3]", "groups[1][questions][0]", or
             "__QUESTION__" in the editor's template, which the script replaces;
    $question: the question (an empty one in the template).
--}}
@php
    $dot = str_replace(['][', '[', ']'], ['.', '.', ''], $prefix);
    $fieldId = trim(preg_replace('/[^A-Za-z0-9_]+/', '-', $prefix), '-');
    $type = old("{$dot}.type", $question->type?->value);
    $options = old("{$dot}.options", $question->options ?? []);
@endphp
<div class="card card-sm mb-3" data-question data-prefix="{{ $prefix }}">
    <div class="card-body">
        @if (! empty($question->id))
            <input type="hidden" name="{{ $prefix }}[id]" value="{{ $question->id }}">
        @endif

        <div class="row g-2 align-items-end">
            <div class="col-md">
                <label class="form-label required" for="{{ $fieldId }}-question">Вопрос</label>
                <input type="text" id="{{ $fieldId }}-question" name="{{ $prefix }}[question]" class="form-control @error("{$dot}.question") is-invalid @enderror"
                       value="{{ old("{$dot}.question", $question->question) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="{{ $fieldId }}-type">Тип вопроса</label>
                <select id="{{ $fieldId }}-type" name="{{ $prefix }}[type]" class="form-select" data-question-type required>
                    @foreach (\App\Enums\QuestionType::cases() as $case)
                        <option value="{{ $case->value }}" @selected($type === $case->value)>{{ $case->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button type="button" class="btn btn-icon btn-outline-danger" data-question-remove aria-label="Удалить вопрос" title="Удалить вопрос">
                    <i class="bx bx-trash" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        @error("{$dot}.question")<div class="invalid-feedback">{{ $message }}</div>@enderror

        <div class="mt-3 @if ($type !== \App\Enums\QuestionType::Choice->value) d-none @endif" data-options>
            <div class="form-label">Варианты ответа</div>
            <div class="vstack gap-2" data-option-list>
                @foreach ($options as $optionIndex => $option)
                    @include('admin.surveys._option', ['prefix' => "{$prefix}[options][{$optionIndex}]", 'option' => $option])
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-option-add><i class="bx bx-plus" aria-hidden="true"></i>Добавить вариант</button>
        </div>
    </div>
</div>
