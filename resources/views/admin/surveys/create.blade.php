@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Создать мед. документ</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('surveys.index') }}">Мед. документы</a> /</span>Создать
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
            <h5 class="mb-0">Создать</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('surveys.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="survey_type">Тип <span class="text-danger">*</span></label>
                    <select name="survey_type_id"
                            class="select2 form-control @error('survey_type_id') is-invalid @enderror"
                            id="survey_type"
                            required>
                        @foreach($types as $type)
                            <option
                                value="{{ $type->id }}" @selected($type->id == old('survey_type_id'))> {{ $type->name }} </option>
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
                               placeholder="Заголовок" aria-label="Заголовок" name="title" value="{{ old('title') }}"
                               aria-describedby="title" required/>
                    </div>
                    @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label d-block">Цвет фона <span class="text-danger">*</span></label>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach(\App\Models\Survey::COLOR as $color)
                            <label class="color-option position-relative" style="cursor: pointer;">
                                <input type="radio" name="color" value="{{ $color }}" class="d-none"
                                    @checked(old('color') === $color)>
                                <span class="color-circle border" style="background-color: {{ $color }};"
                                      title="{{ $color }}"></span>
                            </label>
                        @endforeach
                    </div>
                    @error('color')
                    <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Вопросы</label>
                    <button type="button" id="add-question" class="btn btn-success btn-sm ms-2">Добавить вопрос</button>
                    <button type="button" id="add-group" class="btn btn-primary btn-sm ms-2">Создать группу</button>
                </div>

                <ul class="nav nav-tabs mt-4" id="questionTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="general-tab" data-bs-toggle="tab"
                                data-bs-target="#general-content" type="button" role="tab"
                                aria-controls="general-content" aria-selected="true">
                            Общие вопросы
                        </button>
                    </li>
                </ul>

                <div class="tab-content border border-top-0 p-3" id="questionTabContent">
                    <div class="tab-pane fade show active" id="general-content" role="tabpanel" aria-labelledby="general-tab">
                        <div id="general-questions" class="mt-3"></div>
                    </div>
                </div>


                <button type="submit" class="btn btn-primary mt-3">Сохранить</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            let groupIndex = 0;
            let questionIndex = 0;

            // Добавить группу
            $("#add-group").click(function () {
                let groupTitle = prompt("Введите название группы:");
                if (!groupTitle) return;

                let groupKey = groupTitle.replace(/\s+/g, '_').toLowerCase(); // Безопасный ключ
                let tabId = `tab-${groupKey}`;
                let contentId = `tab-content-${groupKey}`;

                // Не создаём дубликат, если уже есть
                if ($(`#${tabId}`).length > 0) {
                    $(`#${tabId}`).tab("show");
                    return;
                }

                // Вкладка
                $("#questionTabs").append(`
                    <li class="nav-item position-relative" role="presentation" id="tab-wrapper-${groupKey}">
                        <button class="nav-link pe-5" id="${tabId}" data-bs-toggle="tab" data-bs-target="#${contentId}"
                                type="button" role="tab" aria-controls="${contentId}" aria-selected="false">
                            ${groupTitle}
                        </button>
                        <button type="button" class="btn-close position-absolute top-50 translate-middle-y end-0 me-2 remove-group-tab"
                                data-tab-id="${tabId}" data-content-id="${contentId}" aria-label="Удалить"></button>
                        <input type="hidden" name="groups[${groupIndex}][title]" value="${groupTitle}" required>
                    </li>
            `);

                // Контент
                $("#questionTabContent").append(`
                <div class="tab-pane fade" id="${contentId}" role="tabpanel" aria-labelledby="${tabId}">
                    <div class="tab-questions-container mt-3" data-group="${groupKey}"></div>
                </div>
            `);

                // Активируем вкладку
                $(`#${tabId}`).tab("show");

                groupIndex++;
            });

            // Добавить обычный вопрос
            $("#add-question").click(function () {
                const activePane = $(".tab-pane.active");
                const groupKey = activePane.find(".tab-questions-container").data("group") || "general";

                let questionContainer = activePane.find(".tab-questions-container");
                if (questionContainer.length === 0) {
                    // Для вкладки "Общие вопросы"
                    questionContainer = $("#general-questions");
                }

                let groupIdx = getGroupIndex(groupKey);

                const questionHtml = `
                <div class="question-item border p-3 mb-3">
                    <button type="button" class="btn-close float-end remove-question"></button>
                    <div class="mb-2">
                        <label class="form-label">Вопрос <span class="text-danger">*</span></label>
                        <input type="text" name="${groupKey === 'general'
                    ? `questions[${questionIndex}][question]`
                    : `groups[${groupIdx}][questions][${questionIndex}][question]`}" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Тип вопроса</label>
                        <select name="${groupKey === 'general'
                    ? `questions[${questionIndex}][type]`
                    : `groups[${groupIdx}][questions][${questionIndex}][type]`}" class="form-control question-type" required>
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

                questionContainer.append(questionHtml);
                $(".question-type").select2();
                questionIndex++;
            });

            // Удаление вкладки
            $(document).on("click", ".remove-group-tab", function () {
                let tabId = $(this).data("tab-id");
                let contentId = $(this).data("content-id");

                // Удаляем вкладку и контент
                $(`#${tabId}`).closest("li").remove();
                $(`#${contentId}`).remove();

                // Активируем первую вкладку
                $("#questionTabs button:first").tab("show");
            });

            // Удаление вопроса
            $(document).on("click", ".remove-question", function () {
                $(this).closest(".question-item").remove();
            });

            // Показать/скрыть опции
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

            // Добавить опцию
            $(document).on("click", ".add-option", function () {
                let questionItem = $(this).closest(".question-item");
                let optionsContainer = questionItem.find(".option-list");
                let index = optionsContainer.children().length;

                // Определяем группу
                let groupKey = questionItem.closest(".tab-pane").find(".tab-questions-container").data("group") || "general";

                // Индекс группы
                let groupIdx = getGroupIndex(groupKey);

                // Определяем индекс вопроса
                let qNameInput = questionItem.find('input[name$="[question]"]');
                let nameAttr = qNameInput.attr("name");
                let qIndexMatch = nameAttr.match(/\[(\d+)]\[question]/);
                let qIndex = qIndexMatch ? qIndexMatch[1] : 0;

                let optionHtml = `
                    <div class="d-flex align-items-center mb-2 option-item">
                        <input type="text" name="${groupKey === 'general'
                                ? `questions[${qIndex}][options][${index}][text]`
                                : `groups[${groupIdx}][questions][${qIndex}][options][${index}][text]`}"
                               class="form-control me-2" placeholder="Опция" required>
                        <input type="number" name="${groupKey === 'general'
                                ? `questions[${qIndex}][options][${index}][score]`
                                : `groups[${groupIdx}][questions][${qIndex}][options][${index}][score]`}"
                               class="form-control option-score" placeholder="Баллы" min="0">
                        <button type="button" class="btn btn-sm btn-danger ms-1 remove-option">✖</button>
                    </div>
                `;

                optionsContainer.append(optionHtml);
            });

            // Удаление опции
            $(document).on("click", ".remove-option", function () {
                $(this).closest(".option-item").remove();
            });

            // Отправка формы
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

            function getGroupIndex(groupKey) {
                let index = -1;

                $(".tab-questions-container").each(function (i) {
                    if ($(this).data("group") === groupKey) {
                        index = i;
                    }
                });

                return index;
            }
        });
    </script>
@endsection
