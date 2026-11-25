{{--
    A delete action: a DELETE form whose submission app.js confirms first ([data-confirm]).

    <x-delete-button :action="route('regions.destroy', $region)" />
    <x-delete-button :action="..." confirm="Удалить пациента со всей историей?" label="Удалить пациента" />

    Extra attributes (class, title ...) go to the button, a small outlined danger button by default.
--}}
@props([
    'action',
    'confirm' => 'Удалить эту запись? Действие нельзя отменить.',
    'label' => 'Удалить',
])

<form method="POST" action="{{ $action }}" class="d-inline" data-confirm="{{ $confirm }}">
    @csrf
    @method('DELETE')
    <button type="submit" {{ $attributes->class(['btn btn-sm btn-outline-danger']) }}>
        <i class="bx bx-trash" aria-hidden="true"></i>{{ $slot->isEmpty() ? $label : $slot }}
    </button>
</form>
