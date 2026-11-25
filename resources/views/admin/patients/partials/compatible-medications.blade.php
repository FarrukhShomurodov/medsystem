{{--
    The drugs given together with the main drug of a prescription (resources/js/pages/patients/prescriptions.js):
    the select lists the drugs with the same active substance once the main drug is chosen.

    @include('admin.patients.partials.compatible-medications', ['compatible' => $medication->compatibleMedications])
--}}
<fieldset class="compatible-medications">
    <legend class="form-label">Совместимые медикаменты</legend>
    <div class="compatible-list">
        @foreach ($compatible as $drug)
            <div class="input-group compatible-item mb-1">
                <input type="hidden" data-field="compatible_medications" name="compatible_medications[0][]" value="{{ $drug->id }}">
                <input type="hidden" data-field="compatible_dosages" name="compatible_dosages[0][]" value="{{ $drug->pivot->dosage }}">
                <span class="input-group-text flex-grow-1">{{ $drug->name }} ({{ $drug->pivot->dosage }})</span>
                <button type="button" class="btn btn-outline-danger btn-icon btn-remove-compatible" aria-label="Убрать {{ $drug->name }}">
                    <i class="bx bx-x" aria-hidden="true"></i>
                </button>
            </div>
        @endforeach
    </div>
    <div class="row g-2 mt-1">
        <div class="col-md-6">
            <select class="form-select compatible-medication-select" aria-label="Совместимый медикамент">
                <option value="" disabled selected>Выберите медикамент</option>
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" class="form-control compatible-dosage" placeholder="Дозировка" maxlength="50" aria-label="Дозировка совместимого медикамента">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-primary w-100 btn-add-compatible">Добавить</button>
        </div>
    </div>
</fieldset>
