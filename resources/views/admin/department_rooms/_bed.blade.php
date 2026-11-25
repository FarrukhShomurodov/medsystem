{{--
    One bed of the ward room form (a row of a [data-row-list], resources/js/lib/rows.js).
    $name: the name of the number field; $idName (edit only): the hidden field with the bed's id;
    $bed: the bed, or null for a new row.
--}}
<div class="input-group" data-row>
    @isset($idName)
        <input type="hidden" name="{{ $idName }}" value="{{ $bed?->id }}">
    @endisset
    <input type="text" name="{{ $name }}" class="form-control" placeholder="Номер койки" aria-label="Номер койки"
           value="{{ $bed?->bed_number }}" required>
    <button type="button" class="btn btn-icon btn-outline-danger" data-row-remove aria-label="Удалить койку" title="Удалить койку">
        <i class="bx bx-x" aria-hidden="true"></i>
    </button>
</div>
