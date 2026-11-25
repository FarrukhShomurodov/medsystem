{{-- The step list of the patient service wizard; the panes are in _form. --}}
<div class="bs-stepper-header" role="tablist">
    <div class="step" data-target="#basic-data">
        <button type="button" class="step-trigger" id="basic-data-trigger" role="tab" aria-controls="basic-data">
            <span class="bs-stepper-circle">1</span>
            <span class="bs-stepper-label">Основные данные</span>
        </button>
    </div>
    <div class="step" data-target="#details">
        <button type="button" class="step-trigger" id="details-trigger" role="tab" aria-controls="details">
            <span class="bs-stepper-circle">2</span>
            <span class="bs-stepper-label">Детали</span>
        </button>
    </div>
    <div class="step" data-target="#additional_data">
        <button type="button" class="step-trigger" id="additional_data-trigger" role="tab" aria-controls="additional_data">
            <span class="bs-stepper-circle">3</span>
            <span class="bs-stepper-label">Финансовая информация</span>
        </button>
    </div>
</div>
