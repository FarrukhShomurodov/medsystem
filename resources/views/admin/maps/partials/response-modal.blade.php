{{--
    The answers of a filled-in medical document, opened by a .view-response button with data-url
    (route('api.survey-responses.show', $response)): maps/show and maps/surveys.
--}}
<div class="modal modal-blur fade" id="responseModal" tabindex="-1" aria-labelledby="responseModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="responseModalTitle">{{ $title }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body" id="response-content" aria-live="polite"></div>
        </div>
    </div>
</div>
