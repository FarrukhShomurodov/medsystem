{{--
    The identity of a patient: the first step of the patient wizard and the short front-desk form
    (patients/register). Rendered inside a .row.

    @include('admin.patients.partials.basic-fields', ['patient' => $patient])

    patient: the patient being edited, or null for a new one.
--}}
@php
    $basic = fn (string $name, mixed $default = null) => old($name, $patient ? $patient->{$name} : $default);
@endphp

@include('admin.patients.partials.field', ['field' => [
    'name' => 'first_name', 'label' => 'Имя', 'icon' => 'bx-user', 'required' => true, 'value' => $basic('first_name'),
    'attributes' => ['autocomplete' => 'off'],
]])
@include('admin.patients.partials.field', ['field' => [
    'name' => 'last_name', 'label' => 'Фамилия', 'icon' => 'bx-user-circle', 'required' => true, 'value' => $basic('last_name'),
    'attributes' => ['autocomplete' => 'off'],
]])
@include('admin.patients.partials.field', ['field' => [
    'name' => 'middle_name', 'label' => 'Отчество', 'icon' => 'bx-id-card', 'value' => $basic('middle_name'),
    'attributes' => ['autocomplete' => 'off'],
]])
@include('admin.patients.partials.field', ['field' => [
    'name' => 'birth_date', 'label' => 'Дата рождения', 'icon' => 'bx-calendar', 'required' => true,
    'class' => 'flatpickr-date', 'placeholder' => 'ГГГГ-ММ-ДД', 'value' => $basic('birth_date'),
    'attributes' => ['data-max-date' => 'today'],
]])

<div class="col-sm-4">
    <label class="form-label required" for="gender">Пол</label>
    <select class="form-select select2 @error('gender') is-invalid @enderror" id="gender" name="gender" required>
        <option value="" disabled @selected(blank(old('gender', $patient?->gender?->value)))>Выберите пол</option>
        @foreach (\App\Enums\Gender::options() as $genderValue => $genderLabel)
            <option value="{{ $genderValue }}" @selected(old('gender', $patient?->gender?->value) === $genderValue)>{{ $genderLabel }}</option>
        @endforeach
    </select>
    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

@include('admin.patients.partials.field', ['field' => [
    'name' => 'citizenship', 'label' => 'Гражданство', 'icon' => 'bx-flag', 'required' => true,
    'value' => $basic('citizenship', 'Узбекистан'),
]])
@include('admin.patients.partials.field', ['field' => [
    'name' => 'passport_series', 'label' => 'Серия паспорта', 'icon' => 'bx-file', 'required' => true,
    'value' => $basic('passport_series'),
]])
@include('admin.patients.partials.field', ['field' => [
    'name' => 'passport_validity_period', 'label' => 'Срок действия паспорта', 'icon' => 'bx-calendar', 'required' => true,
    'class' => 'flatpickr-date', 'placeholder' => 'ГГГГ-ММ-ДД', 'value' => $basic('passport_validity_period'),
]])

<div class="col-sm-4">
    <label class="form-label" for="files">Файлы</label>
    <input type="file" name="files[]" id="files" multiple
           accept=".{{ implode(',.', \App\Http\Requests\PatientRequest::FILE_TYPES) }}"
           class="form-control @error('files') is-invalid @enderror @error('files.*') is-invalid @enderror">
    @error('files')<div class="invalid-feedback">{{ $message }}</div>@enderror
    @error('files.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

@include('admin.patients.partials.field', ['field' => [
    'name' => 'referral_source', 'label' => 'Откуда узнали о нас?', 'icon' => 'bx-help-circle', 'col' => 'col-sm-8',
    'value' => $basic('referral_source'),
]])
