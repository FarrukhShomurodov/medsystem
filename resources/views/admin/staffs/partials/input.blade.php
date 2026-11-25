{{--
    One text field of the staff form with its icon, label and error:

    @include('admin.staffs.partials.input', ['field' => [
        'name' => 'last_name', 'label' => 'Фамилия', 'icon' => 'bx-rename', 'value' => $staff?->last_name, 'required' => true,
    ]])

    Optional keys: type (text), class (extra input classes, e.g. flatpickr-date), placeholder (the label),
    col (col-md-6 col-xxl-4), autocomplete, inputmode. The id is the field name. The settings come in one array
    because an @include also sees the variables of the including view.
--}}
@php
    $name = $field['name'];
    $required = $field['required'] ?? false;
    $invalid = $errors->has($name);
@endphp
<div class="{{ $field['col'] ?? 'col-md-6 col-xxl-4' }}">
    <label @class(['form-label', 'required' => $required]) for="{{ $name }}">{{ $field['label'] }}</label>
    <div class="input-group input-group-flat">
        <span class="input-group-text"><i class="bx {{ $field['icon'] }}" aria-hidden="true"></i></span>
        <input type="{{ $field['type'] ?? 'text' }}" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $field['value'] ?? null) }}"
               @class(['form-control', $field['class'] ?? null, 'is-invalid' => $invalid])
               placeholder="{{ $field['placeholder'] ?? $field['label'] }}"
               @isset($field['autocomplete']) autocomplete="{{ $field['autocomplete'] }}" @endisset
               @isset($field['inputmode']) inputmode="{{ $field['inputmode'] }}" @endisset
               @required($required)
               @if($invalid) aria-describedby="{{ $name }}-error" aria-invalid="true" @endif>
    </div>
    @error($name)
        <div class="invalid-feedback" id="{{ $name }}-error">{{ $message }}</div>
    @enderror
</div>
