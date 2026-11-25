{{--
    One absence of the employee: absence[<n>][type|date|reason]. The date is one day "Y-m-d" or a
    period "Y-m-d to Y-m-d" (StaffRequest splits it); staff-form.js gives the field its picker and
    switches it between one day and a period with the type.

    @include('admin.staffs.partials.absence', ['absence' => ['index' => 0, 'type' => $type, 'date' => '2026-12-14', 'reason' => $reason]])

    With 'template' => true it renders the row of the <template> that staff-form.js copies (no names or ids).
--}}
@php
    $template = $absence['template'] ?? false;
    $id = $template ? null : "absence-{$absence['index']}";
    $name = $template ? null : "absence[{$absence['index']}]";
    $type = $absence['type'] ?? null;
    $reason = $absence['reason'] ?? null;
@endphp
<div class="row g-2 align-items-end mb-3" data-absence>
    <div class="col-sm-4">
        <label class="form-label" @if($id) for="{{ $id }}-type" @endif data-label-for="type">Тип</label>
        <select class="form-select select2" data-field="type" data-absence-type
                @unless($template) id="{{ $id }}-type" name="{{ $name }}[type]" @endunless>
            @foreach(\App\Enums\AbsenceType::cases() as $case)
                <option value="{{ $case->value }}" @selected($type === $case)>{{ $case->label() }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-4">
        <label class="form-label" @if($id) for="{{ $id }}-date" @endif data-label-for="date">Дата или период</label>
        <input type="text" class="form-control absence-date" data-field="date" placeholder="Выберите дату" required
               @unless($template) id="{{ $id }}-date" name="{{ $name }}[date]" value="{{ $absence['date'] }}" @endunless>
    </div>
    <div class="col">
        <label class="form-label" @if($id) for="{{ $id }}-reason" @endif data-label-for="reason">Причина</label>
        <select class="form-select select2" data-field="reason"
                @unless($template) id="{{ $id }}-reason" name="{{ $name }}[reason]" @endunless>
            @foreach(\App\Enums\AbsenceReason::cases() as $case)
                <option value="{{ $case->value }}" @selected($reason === $case)>{{ $case->label() }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <button type="button" class="btn btn-icon btn-outline-danger" data-remove-absence
                aria-label="Удалить нерабочий день" title="Удалить нерабочий день">
            <i class="bx bx-trash" aria-hidden="true"></i>
        </button>
    </div>
</div>
