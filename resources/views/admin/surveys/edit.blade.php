@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Редактировать опросник</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('surveys.index') }}">Опросники</a> /</span>Редактировать
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
                                value="{{ $type->id }}" @selected($type->id == old('survey_type_id', $survey->survey_type_id))>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('survey_type_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="title">Заголовок <span class="text-danger">*</span></label>
                    <div class="input-group input-group-merge">
                        <span id="title" class="input-group-text @error('title') text-danger icon-border @enderror">
                            <i class="bx bx-rename"></i>
                        </span>
                        <input type="text" id="title"
                               class="form-control @error('title') is-invalid @enderror"
                               placeholder="Заголовок" name="title"
                               value="{{ old('title', $survey->title) }}" required/>
                    </div>
                    @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Вопросы</label>
                    <button type="button" id="add-question" class="btn btn-success btn-sm ms-2">Добавить вопрос</button>
                </div>

                <div id="questions-container">
                    @foreach ($survey->questions as $index => $question)
                        <div class="question-item border p-3 mb-3" data-id="{{ $question->id }}">
                            <button type="button" class="btn-close float-end remove-question"></button>
                            <input type="hidden" name="questions[{{ $index }}][id]" value="{{ $question->id }}">

                            <div class="mb-2">
                                <label class="form-label">Вопрос <span class="text-danger">*</span></label>
                                <input type="text" name="questions[{{ $index }}][question]" class="form-control"
                                       value="{{ old("questions.$index.question", $question->question) }}" required>
                            </div>

                            <div class="mb-2">
                                <label class="form-label" for="form-label-{{$index}}">Тип вопроса</label>
                                <select id="form-label-{{$index}}" name="questions[{{ $index }}][type]"
                                        class="form-control question-type select2" required>
                                    <option value="boolean" @selected($question->type == 'boolean')>Да / Нет</option>
                                    <option value="choice" @selected($question->type == 'choice')>Список</option>
                                    <option value="text" @selected($question->type == 'text')>Текст</option>
                                </select>
                            </div>

                            <div class="options-container @if($question->type !== 'choice') d-none @endif">
                                <div class="options-container">
                                    <label class="form-label">Опции</label>
                                    <div class="option-list">
                                        @foreach($question->options as $optionIndex => $option)
                                            <div class="d-flex align-items-center mb-2 option-item">
                                                <input type="text"
                                                       name="questions[{{ $index }}][options][{{ $optionIndex }}][text]"
                                                       value="{{ $option['text'] }}"
                                                       class="form-control me-2" placeholder="Опция" required>
                                                <input type="number"
                                                       name="questions[{{ $index }}][options][{{ $optionIndex }}][score]"
                                                       value="{{ $option['score'] }}"
                                                       class="form-control option-score" placeholder="Баллы" min="0">
                                                <button type="button" class="btn btn-sm btn-danger ms-1 remove-option">✖
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>

                                    <button type="button" class="btn btn-sm btn-outline-primary add-option">Добавить
                                        опцию
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn btn-warning mt-3">Редактировать</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            let questionIndex = {{ count($survey->questions) }};

            $("#add-question").click(function () {
                let questionHtml = `
                    <div class="question-item border p-3 mb-3">
                        <button type="button" class="btn-close float-end remove-question"></button>
                        <div class="mb-2">
                            <label class="form-label">Вопрос <span class="text-danger">*</span></label>
                            <input type="text" name="questions[${questionIndex}][question]" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Тип вопроса</label>
                            <select name="questions[${questionIndex}][type]" class="form-control question-type" required>
                                <option value="boolean">Да / Нет</option>
                                <option value="choice">Список</option>
                                <option value="text">Текст</option>
                            </select>
                        </div>
                         <div class="options-container d-none">
                            <label class="form-label">Опции</label>
                            <div class="option-list"></div>
                            <button type="button" class="btn btn-sm btn-outline-primary add-option">Добавить опцию</button>
                        </div>
                    </div>
                `;

                $("#questions-container").append(questionHtml);
                $(".question-type").select2();
                questionIndex++;
            });

            $(document).on("change", ".question-type", function () {
                let parent = $(this).closest(".question-item");
                let optionsContainer = parent.find(".options-container");

                if ($(this).val() === "choice") {
                    optionsContainer.removeClass("d-none");
                } else {
                    optionsContainer.addClass("d-none");
                    optionsContainer.find(".option-list").empty();
                }
            });

            $(document).on("click", ".add-option", function () {
                let optionsContainer = $(this).siblings(".option-list");
                let index = optionsContainer.children().length;

                let optionHtml = `
                    <div class="d-flex align-items-center mb-2 option-item">
                        <input type="text" name="questions[${questionIndex - 1}][options][${index}][text]"
                               class="form-control me-2" placeholder="Опция" required>
                        <input type="number" name="questions[${questionIndex - 1}][options][${index}][score]"
                               class="form-control option-score" placeholder="Баллы" min="0">
                        <button type="button" class="btn btn-sm btn-danger ms-1 remove-option">✖</button>
                    </div>
                `;

                optionsContainer.append(optionHtml);
            });

            $(document).on("click", ".remove-option", function () {
                $(this).closest(".option-item").remove();
            });

            $('form').submit(function () {
                $('.question-item').each(function () {
                    let options = [];
                    $(this).find('.option-item').each(function () {
                        let text = $(this).find('input[name$="[text]"]').val().trim();
                        let score = $(this).find('input[name$="[score]"]').val().trim();
                        if (text !== '') {
                            options.push({text: text, score: score ? parseInt(score) : 0});
                        }
                    });

                    if (options.length > 0) {
                        $(this).find('input[name$="[options]"]').val(JSON.stringify(options));
                    }
                });
            });
        });
    </script>
@endsection
