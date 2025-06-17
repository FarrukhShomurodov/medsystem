@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Редактировать мед. документ</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted" href="{{ route('surveys.index') }}">Мед. документы</a> /</span>Редактировать
    </h6>

    <div class="alert-container position-fixed top-0 end-0 p-3" style="z-index: 10050;">
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <div class="alert alert-solid-danger alert-dismissible d-flex align-items-center" role="alert">
                    <i class="bx bx-error-circle fs-4 me-2"></i>
                    {{ $error }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endforeach
        @endif
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Редактировать</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('surveys.update', $survey->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label" for="survey_type">Тип <span class="text-danger">*</span></label>
                    <select name="survey_type_id"
                            class="select2 form-control @error('survey_type_id') is-invalid @enderror"
                            id="survey_type"
                            required>
                        @foreach($types as $type)
                            <option
                                value="{{ $type->id }}" @selected($type->id == old('survey_type_id', $survey->survey_type_id))> {{ $type->name }} </option>
                        @endforeach
                    </select>
                    @error('survey_type_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="title">Заголовок <span class="text-danger">*</span></label>
                    <div class="input-group input-group-merge">
                        <span id="title"
                              class="input-group-text @error('title') text-danger icon-border @enderror">
                            <i class="bx bx-rename"></i>
                        </span>
                        <input type="text" id="title"
                               class="form-control @error('title') is-invalid @enderror"
                               placeholder="Заголовок" aria-label="Заголовок" name="title"
                               value="{{ old('title', $survey->title) }}"
                               aria-describedby="title" required/>
                    </div>
                    @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Вопросы</label>
                    <button type="button" id="add-question" class="btn btn-success btn-sm ms-2">Добавить вопрос</button>
                    <button type="button" id="add-group" class="btn btn-primary btn-sm ms-2">Создать группу</button>
                </div>

                <ul class="nav nav-tabs mt-4" id="questionTabs" role="tablist">
                    <li class="nav-item position-relative" role="presentation" id="tab-wrapper-general">
                        <button class="nav-link active" id="tab-general" data-bs-toggle="tab"
                                data-bs-target="#tab-content-general" type="button" role="tab"
                                aria-controls="tab-content-general" aria-selected="true">
                            Общие вопросы
                        </button>
                    </li>

                    @foreach($survey->groups as $group)
                        <li class="nav-item position-relative" role="presentation" id="tab-wrapper-{{ $group->title }}">
                            <button class="nav-link" id="tab-{{ $group->title }}" data-bs-toggle="tab"
                                    data-bs-target="#tab-content-{{ $group->title }}" type="button" role="tab"
                                    aria-controls="tab-content-{{ $group->title }}" aria-selected="false">
                                {{ $group->title }}
                            </button>
                            <button type="button"
                                    class="btn-close position-absolute top-50 translate-middle-y end-0 remove-group-tab"
                                    data-tab-id="tab-{{ $group->title }}"
                                    data-content-id="tab-content-{{ $group->title }}" aria-label="Удалить"></button>
                            <input type="hidden" name="groups[{{ $loop->index }}][title]" value="{{ $group->title }}"
                                   required>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content border border-top-0 p-3" id="questionTabContent">
                    <div class="tab-pane fade show active" id="tab-content-general" role="tabpanel"
                         aria-labelledby="tab-general">
                        <div class="tab-questions-container mt-3" data-group="general">
                            @foreach($survey->questions as $question)
                                <div class="question-item border p-3 mb-3">
                                    <button type="button" class="btn-close float-end remove-question"></button>
                                    <input type="hidden" name="groups[general][questions][{{ $loop->index }}][id]"
                                           value="{{ $question->id }}">
                                    <div class="mb-2">
                                        <label class="form-label">Вопрос <span class="text-danger">*</span></label>
                                        <input type="text"
                                               name="groups[general][questions][{{ $loop->index }}][question]"
                                               class="form-control" value="{{ old('question', $question->question) }}"
                                               required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Тип вопроса</label>
                                        <select name="groups[general][questions][{{ $loop->index }}][type]"
                                                class="form-control question-type" required>
                                            <option value="boolean" @selected($question->type == 'boolean')>Да / Нет
                                            </option>
                                            <option value="choice" @selected($question->type == 'choice')>Список
                                            </option>
                                            <option value="text" @selected($question->type == 'text')>Текст</option>
                                        </select>
                                    </div>
                                    <div class="options-container @if($question->type !== 'choice') d-none @endif">
                                        <label class="form-label">Опции</label>
                                        <div class="option-list">
                                            @foreach($question->options ?? [] as $optionIndex => $option)
                                                <div class="d-flex align-items-center mb-2 option-item">
                                                    <input type="text"
                                                           name="groups[general][questions][{{ $loop->parent->index }}][options][{{ $optionIndex }}][text]"
                                                           value="{{ $option['text'] }}" class="form-control me-2"
                                                           placeholder="Опция" required>
                                                    <input type="number"
                                                           name="groups[general][questions][{{ $loop->parent->index }}][options][{{ $optionIndex }}][score]"
                                                           value="{{ $option['score'] }}" class="form-control"
                                                           placeholder="Баллы" min="0">
                                                    <button type="button"
                                                            class="btn btn-sm btn-danger ms-1 remove-option">✖
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary add-option">Добавить
                                            опцию
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @foreach($survey->groups as $group)
                        <div class="tab-pane fade" id="tab-content-{{ $group->title }}" role="tabpanel"
                             aria-labelledby="tab-{{ $group->title }}">
                            <div class="tab-questions-container mt-3">
                                @foreach($group->questions as $question)
                                    <div class="question-item border p-3 mb-3">
                                        <button type="button" class="btn-close float-end remove-question"></button>
                                        <input type="hidden"
                                               name="groups[{{ $loop->parent->index }}][questions][{{ $loop->index }}][id]"
                                               value="{{ $question->id }}">
                                        <div class="mb-2">
                                            <label class="form-label">Вопрос <span class="text-danger">*</span></label>
                                            <input type="text"
                                                   name="groups[{{ $loop->parent->index }}][questions][{{ $loop->index }}][question]"
                                                   class="form-control"
                                                   value="{{ old('question', $question->question) }}" required>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Тип вопроса</label>
                                            <select
                                                name="groups[{{ $loop->parent->index }}][questions][{{ $loop->index }}][type]"
                                                class="form-control question-type" required>
                                                <option value="boolean" @selected($question->type == 'boolean')>Да /
                                                    Нет
                                                </option>
                                                <option value="choice" @selected($question->type == 'choice')>Список
                                                </option>
                                                <option value="text" @selected($question->type == 'text')>Текст</option>
                                            </select>
                                        </div>
                                        <div class="options-container @if($question->type !== 'choice') d-none @endif">
                                            <label class="form-label">Опции</label>
                                            <div class="option-list">
                                                @foreach($question->options ?? [] as $optionIndex => $option)
                                                    <div class="d-flex align-items-center mb-2 option-item">
                                                        <input type="text"
                                                               name="groups[{{ $loop->parent->index }}][questions][{{ $loop->index }}][options][{{ $optionIndex }}][text]"
                                                               value="{{ $option['text'] }}" class="form-control me-2"
                                                               placeholder="Опция" required>
                                                        <input type="number"
                                                               name="groups[{{ $loop->parent->index }}][questions][{{ $loop->index }}][options][{{ $optionIndex }}][score]"
                                                               value="{{ $option['score'] }}" class="form-control"
                                                               placeholder="Баллы" min="0">
                                                        <button type="button"
                                                                class="btn btn-sm btn-danger ms-1 remove-option">✖
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-primary add-option">
                                                Добавить опцию
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-primary mt-3">Сохранить</button>
            </form>
        </div>
    </div>
@endsection
