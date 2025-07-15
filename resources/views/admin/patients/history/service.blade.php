@php use App\Models\PatientService; @endphp
<div class="border-bottom pb-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">
            <i class="bi bi-clipboard-data me-2 text-info"></i>
            {{ $data->service->name }}
        </h5>
        <div>
            {!! $data->getStatusBadge() !!}
            <span class="text-muted">
                {{ \Carbon\Carbon::parse($data->created_at)->format('d.m.Y') }}
            </span>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="mb-3">
                <small class="text-muted d-block mb-1">
                    <i class="bi bi-clock-history me-1"></i>
                    Дата оказания
                </small>
                <div class="fs-5">
                    {{ \Carbon\Carbon::parse($data->service_date)->format('d.m.Y') }}
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="mb-3">
                <small class="text-muted d-block mb-1">
                    <i class="bi bi-geo-alt me-1"></i>
                    Место оказания
                </small>
                <div class="fs-5">
                    {{ $data->place == 'institution' ? 'Учреждение' : 'Кабинет'}}
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <label class="form-label" for="service_status">Статус</label>
            <select id="service_status" class="form-control select2 service_status" data-service-id="{{ $data->id }}">
                @foreach(PatientService::STATUS as $status)
                    <option value="{{ $status }}" @selected($data->status == $status)>
                        {{ PatientService::getStatusTranslate($status)  }}
                    </option>
                @endforeach
            </select>
        </div>

        @if($data->result)
            <div class="col-12">
                <hr class="my-2">
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">
                        <i class="bi bi-file-earmark-text me-1"></i>
                        Результат
                    </small>
                    <div class="fst-italic">
                        {{ $data->result }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@if($data->surveyResponses()->count() > 0)
    @foreach($data->surveyResponses as $response)
        @include('admin.patients.history.patient_med_document', ['data' => $response])
    @endforeach
@endif

<div class="mt-3 mb-3 text-end">
    <button type="button"
            class="btn btn-sm btn-outline-primary"
            data-bs-toggle="modal"
            data-bs-target="#createDocumentModal"
            data-service-id="{{ $data->id }}">
        <i class="bi bi-paperclip me-1"></i>
        Прикрепить документ
    </button>
</div>

<script>
    $('.service_status').on('change', function () {
        const serviceId = $(this).data('service-id');
        const newStatus = $(this).val();

        $.ajax({
            url: `/api/patient/service/update-status/${serviceId}`,
            method: 'GET',
            data: {
                status: newStatus
            },
            beforeSend: function () {
                // Блокировка элемента на время запроса
                $('#service_status').prop('disabled', true);
            },
            success: function (response) {
                window.location.reload();
            },
            error: function (xhr) {

                // Возвращаем предыдущее значение
                $('#service_status').val(triggeredStatus).trigger('change');
            },
            complete: function () {
                $('#service_status').prop('disabled', false);
            }
        });
    });
</script>
