{{--
    The medical document editor (create and edit), inside its <form>; $survey is null on create.
    Questions without a group are posted as questions[i], those of a group as groups[g][questions][i];
    existing groups and questions carry their id. resources/js/pages/survey-editor.js adds and
    removes them from the <template>s at the end.
--}}
@php
    $groups = $survey?->groups->sortBy('id') ?? collect();
    $generalQuestions = $survey?->questions->whereNull('group_id') ?? collect();
    $color = old('color', $survey?->color);
@endphp

<div class="card-body">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label required" for="survey_type_id">Тип</label>
            <select id="survey_type_id" name="survey_type_id" class="form-select select2 @error('survey_type_id') is-invalid @enderror" required>
                @foreach ($types as $type)
                    <option value="{{ $type->id }}" @selected($type->id == old('survey_type_id', $survey?->survey_type_id))>{{ $type->name }}</option>
                @endforeach
            </select>
            @error('survey_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-8">
            <label class="form-label required" for="title">Заголовок</label>
            <div class="input-group input-group-flat">
                <span class="input-group-text"><i class="bx bx-rename" aria-hidden="true"></i></span>
                <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror"
                       placeholder="Заголовок" value="{{ old('title', $survey?->title) }}" required>
            </div>
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <fieldset class="col-12">
            <legend class="form-label required">Цвет фона</legend>
            <div class="row g-2">
                @foreach (\App\Models\Survey::COLOR as $option)
                    <div class="col-auto">
                        <label class="form-colorinput form-colorinput-light" title="{{ $option }}">
                            <input type="radio" name="color" value="{{ $option }}" class="form-colorinput-input"
                                   aria-label="Цвет {{ $option }}" @checked($color === $option) required>
                            <span class="form-colorinput-color survey-color">@include('admin.surveys._swatch', ['color' => $option])</span>
                        </label>
                    </div>
                @endforeach
            </div>
            @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </fieldset>
    </div>
</div>

<div class="card-body border-top"
     data-survey-editor
     data-choice-type="{{ \App\Enums\QuestionType::Choice->value }}"
     data-next-group="{{ $groups->count() }}"
     data-next-question="{{ $survey?->questions->count() ?? 0 }}">
    <div class="d-flex flex-wrap align-items-end gap-3 mb-3">
        <h2 class="card-title me-auto mb-0">Вопросы</h2>
        <div>
            <label class="form-label" for="new-group-title">Новая группа вопросов</label>
            <div class="input-group">
                <input type="text" id="new-group-title" class="form-control" placeholder="Название группы" data-group-new-title>
                <button type="button" class="btn" data-group-add><i class="bx bx-folder-plus" aria-hidden="true"></i>Создать группу</button>
            </div>
        </div>
    </div>
    @error('questions')<div class="invalid-feedback mb-3">{{ $message }}</div>@enderror

    <ul class="nav nav-tabs" role="tablist" data-group-tabs>
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general-content"
                    role="tab" aria-controls="general-content" aria-selected="true">Общие вопросы</button>
        </li>
        @foreach ($groups as $index => $group)
            @include('admin.surveys._group-tab', ['index' => $index, 'title' => $group->title])
        @endforeach
    </ul>

    <div class="tab-content pt-3" data-group-panes>
        <div class="tab-pane active show" id="general-content" role="tabpanel" aria-labelledby="general-tab" tabindex="0">
            <div data-question-list data-prefix="questions">
                @foreach ($generalQuestions as $index => $question)
                    @include('admin.surveys._question', ['question' => $question, 'prefix' => "questions[{$index}]"])
                @endforeach
            </div>
            <button type="button" class="btn btn-outline-primary" data-question-add><i class="bx bx-plus" aria-hidden="true"></i>Добавить вопрос</button>
        </div>

        @foreach ($groups as $index => $group)
            @include('admin.surveys._group-pane', ['index' => $index, 'group' => $group])
        @endforeach
    </div>

    <template data-survey-template="question">
        @include('admin.surveys._question', ['question' => new \App\Models\SurveyQuestion, 'prefix' => '__QUESTION__'])
    </template>
    <template data-survey-template="option">
        @include('admin.surveys._option', ['prefix' => '__OPTION__', 'option' => []])
    </template>
    <template data-survey-template="group-tab">
        @include('admin.surveys._group-tab', ['index' => '__GROUP__', 'title' => ''])
    </template>
    <template data-survey-template="group-pane">
        @include('admin.surveys._group-pane', ['index' => '__GROUP__', 'group' => null])
    </template>
</div>
