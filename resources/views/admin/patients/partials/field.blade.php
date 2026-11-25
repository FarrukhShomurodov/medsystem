{{--
    A labelled field of the patient and examination-map forms, in the dashboard's form markup:

    @include('admin.patients.partials.field', ['field' => [
        'name' => 'first_name', 'label' => 'Имя', 'icon' => 'bx-user', 'required' => true,
        'value' => old('first_name', $patient?->first_name),
    ]])

    Keys: name, label, value; optional: id (the name by default), type (text, email, number or
    textarea), icon (a Boxicons class: the field gets an addon), required, class (extra classes of
    the field: flatpickr-date, ...), col (the grid column, col-sm-4 by default), placeholder (the
    label by default), attributes (extra attributes of the field: ['autocomplete' => 'tel']).
    The parameters travel in one array: an included view also sees every variable of its parent.
--}}
@php
    $fieldId = $field['id'] ?? $field['name'];
    $fieldType = $field['type'] ?? 'text';
    $fieldIcon = $field['icon'] ?? null;
    $fieldClass = trim('form-control '.($field['class'] ?? '').($errors->has($field['name']) ? ' is-invalid' : ''));
    $fieldAttributes = new \Illuminate\View\ComponentAttributeBag(array_filter([
        'id' => $fieldId,
        'name' => $field['name'],
        'class' => $fieldClass,
        'placeholder' => $field['placeholder'] ?? $field['label'],
        'required' => ($field['required'] ?? false) ?: null,
        ...($field['attributes'] ?? []),
    ], fn ($value) => $value !== null && $value !== false));
@endphp
<div class="{{ $field['col'] ?? 'col-sm-4' }}">
    <label @class(['form-label', 'required' => $field['required'] ?? false]) for="{{ $fieldId }}">{{ $field['label'] }}</label>
    @if ($fieldIcon)
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx {{ $fieldIcon }}" aria-hidden="true"></i></span>
    @endif
    @if ($fieldType === 'textarea')
        <textarea {{ $fieldAttributes->merge(['rows' => 3]) }}>{{ $field['value'] ?? '' }}</textarea>
    @else
        <input type="{{ $fieldType }}" {{ $fieldAttributes }} value="{{ $field['value'] ?? '' }}">
    @endif
    @if ($fieldIcon)
        </div>
    @endif
    @error($field['name'])<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
