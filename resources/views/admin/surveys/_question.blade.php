<div class="question-item border p-3 mb-3">
    <button type="button" class="btn-close float-end remove-question"></button>

    @if(!empty($question->id))
        <input type="hidden" name="{{ $prefix }}[id]" value="{{ $question->id }}">
    @endif

    <div class="mb-2">
        <label class="form-label">Вопрос <span class="text-danger">*</span></label>
        <input type="text" name="{{ $prefix }}[question]" class="form-control"
               value="{{ old("$prefix.question", $question->question) }}" required>
    </div>

    <div class="mb-2">
        <label class="form-label">Тип вопроса</label>
        <select name="{{ $prefix }}[type]" class="form-control question-type select2" required>
            <option value="boolean" @selected($question->type == 'boolean')>Да / Нет</option>
            <option value="choice" @selected($question->type == 'choice')>Список</option>
            <option value="text" @selected($question->type == 'text')>Текст</option>
        </select>
    </div>

    <div class="options-container @if($question->type !== 'choice') d-none @endif">
        <label class="form-label">Опции</label>
        <div class="option-list">
            @foreach($question->options ?? [] as $optionIndex => $option)
                <div class="d-flex align-items-center mb-2 option-item">
                    <input type="text" name="{{ $prefix }}[options][{{ $optionIndex }}][text]"
                           value="{{ $option['text'] }}" class="form-control me-2" placeholder="Опция" required>
                    <input type="number" name="{{ $prefix }}[options][{{ $optionIndex }}][score]"
                           value="{{ $option['score'] }}" class="form-control" placeholder="Баллы" min="0">
                    <button type="button" class="btn btn-sm btn-danger ms-1 remove-option">✖</button>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary add-option">Добавить опцию</button>
    </div>
</div>
