{{--
    One subgroup of the service group form (a row of a [data-row-list], resources/js/lib/rows.js).
    $name: the name of the title field; $idName (edit only): the hidden field with the subgroup's id;
    $subGroup: the subgroup, or null for a new row.
--}}
<div class="input-group" data-row>
    @isset($idName)
        <input type="hidden" name="{{ $idName }}" value="{{ $subGroup?->id }}">
    @endisset
    <input type="text" name="{{ $name }}" class="form-control" placeholder="Название подгруппы" aria-label="Название подгруппы"
           value="{{ $subGroup?->name }}" required>
    <button type="button" class="btn btn-icon btn-outline-danger" data-row-remove aria-label="Удалить подгруппу" title="Удалить подгруппу">
        <i class="bx bx-x" aria-hidden="true"></i>
    </button>
</div>
