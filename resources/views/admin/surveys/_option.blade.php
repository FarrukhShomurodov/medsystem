{{--
    One answer option of a choice question in the survey editor.
    $prefix: "questions[3][options][0]", or "__OPTION__" in the editor's template; $option: ['text', 'score'].
--}}
<div class="input-group" data-option>
    <input type="text" name="{{ $prefix }}[text]" class="form-control" placeholder="Вариант ответа" aria-label="Вариант ответа"
           value="{{ $option['text'] ?? '' }}" required>
    <input type="number" name="{{ $prefix }}[score]" class="form-control survey-option-score" placeholder="Баллы" aria-label="Баллы"
           min="0" step="1" value="{{ $option['score'] ?? '' }}">
    <button type="button" class="btn btn-icon btn-outline-danger" data-option-remove aria-label="Удалить вариант" title="Удалить вариант">
        <i class="bx bx-x" aria-hidden="true"></i>
    </button>
</div>
