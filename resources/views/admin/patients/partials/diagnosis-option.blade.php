{{--
    One row of the patient card's diagnosis picker: add the diagnosis, or star it as a favourite.
    Without a diagnosis it is the <template> row patient-card.js fills in for a search result.
--}}
<div class="list-group-item d-flex align-items-center gap-2 diagnosis-option">
    <button type="button" class="btn btn-link p-0 text-start flex-grow-1 diagnosis-item" data-diagnosis-id="{{ $diagnosis?->id }}">
        <span data-diagnosis-name>{{ $diagnosis?->name }}</span> <span class="text-secondary ms-1" data-diagnosis-code>{{ $diagnosis?->code }}</span>
    </button>
    <button type="button" class="btn btn-icon btn-ghost-warning diagnosis-favorite"
            data-diagnosis-id="{{ $diagnosis?->id }}" aria-pressed="{{ $favorite ? 'true' : 'false' }}"
            aria-label="{{ $favorite ? 'Убрать из избранного' : 'Добавить в избранное' }}"
            title="{{ $favorite ? 'Убрать из избранного' : 'Добавить в избранное' }}">
        <i class="bx {{ $favorite ? 'bxs-star' : 'bx-star' }}" aria-hidden="true"></i>
    </button>
</div>
