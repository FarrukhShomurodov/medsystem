{{-- Text fields of a medication form step: $fields is name => [label, type ("date" for a date picker), icon, value, column, required]. --}}
@foreach ($fields as $name => [$label, $type, $icon, $value, $column, $required])
    <div class="{{ $column }}">
        <label @class(['form-label', 'required' => $required]) for="{{ $name }}">{{ $label }}</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx {{ $icon }}" aria-hidden="true"></i></span>
            <input type="{{ $type === 'date' ? 'text' : $type }}" id="{{ $name }}" name="{{ $name }}"
                   @class(['form-control', 'flatpickr-date' => $type === 'date', 'is-invalid' => $errors->has($name)])
                   value="{{ old($name, $value) }}" @if ($type === 'date') placeholder="ГГГГ-ММ-ДД" @endif @required($required)>
        </div>
        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
@endforeach
