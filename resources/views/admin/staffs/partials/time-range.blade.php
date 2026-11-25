{{--
    One interval of a day of the staff schedule: days[<day>][work|reception][<n>][start_time|end_time].

    @include('admin.staffs.partials.time-range', ['range' => [
        'day' => 0, 'type' => 'work', 'index' => 0, 'start' => '08:30', 'end' => '17:30', 'disabled' => false,
    ]])

    With 'template' => true it renders the row of the <template> that staff-form.js copies: no names,
    ids or values, the script sets them (data-field names the part of the field).
--}}
@php
    $template = $range['template'] ?? false;
    $id = $template ? null : "days-{$range['day']}-{$range['type']}-{$range['index']}";
    $name = $template ? null : "days[{$range['day']}][{$range['type']}][{$range['index']}]";
    $disabled = $range['disabled'] ?? false;
@endphp
<div class="row g-2 align-items-end mb-2" data-time-range>
    <div class="col">
        <label class="form-label" @if($id) for="{{ $id }}-start" @endif data-label-for="start_time">Начало</label>
        <input type="time" class="form-control" data-field="start_time" required
               @unless($template) id="{{ $id }}-start" name="{{ $name }}[start_time]" value="{{ $range['start'] }}" @endunless
               @disabled($disabled)>
    </div>
    <div class="col">
        <label class="form-label" @if($id) for="{{ $id }}-end" @endif data-label-for="end_time">Конец</label>
        <input type="time" class="form-control" data-field="end_time" required
               @unless($template) id="{{ $id }}-end" name="{{ $name }}[end_time]" value="{{ $range['end'] }}" @endunless
               @disabled($disabled)>
    </div>
    <div class="col-auto">
        <button type="button" class="btn btn-icon btn-outline-danger" data-remove-range
                aria-label="Удалить интервал" title="Удалить интервал" @disabled($disabled)>
            <i class="bx bx-x" aria-hidden="true"></i>
        </button>
    </div>
</div>
