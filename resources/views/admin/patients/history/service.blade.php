{{-- A service of the patient card history (api.patients.history.partial). patient-card.js sends the status select. --}}
<div class="border-bottom pb-3 mb-3">
    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0">
            <i class="bx bx-clipboard text-info me-2" aria-hidden="true"></i>{{ $data->service?->name }}
        </h3>
        <div class="text-nowrap">
            <x-status-badge :status="$data->status" class="me-1" />
            <span class="text-secondary">{{ \Carbon\Carbon::parse($data->created_at)->format('d.m.Y') }}</span>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="datagrid-title"><i class="bx bx-history me-1" aria-hidden="true"></i>Дата оказания</div>
            <div class="fs-3">{{ \Carbon\Carbon::parse($data->service_date)->format('d.m.Y') }}</div>
        </div>

        <div class="col-md-4">
            <div class="datagrid-title"><i class="bx bx-map-pin me-1" aria-hidden="true"></i>Место оказания</div>
            <div class="fs-3">{{ $data->place?->label() ?? '—' }}</div>
        </div>

        {{-- The status route (api.patient-services.update-status) needs edit_patient_services and accepts only
             the next statuses of the order, never "paid": payment is taken at the cash desk. The badge above
             shows the status to everyone. A final status cannot be left again, so patient-card.js asks
             the question of its option (data-question, data-accept) before it sends one. --}}
        @php
            $status = $data->status;
            $serviceName = $data->service?->name ?? 'без названия';
            $confirmations = [
                \App\Enums\ServiceStatus::Cancelled->value => [
                    "Отменить заказ услуги «{$serviceName}»? Отменённый заказ нельзя вернуть, услугу придётся назначить заново.",
                    'Отменить услугу',
                ],
                \App\Enums\ServiceStatus::Completed->value => [
                    "Завершить услугу «{$serviceName}»? Статус больше нельзя будет изменить.",
                    'Завершить услугу',
                ],
            ];
        @endphp
        @if (! $status->isFinal())
            @can('edit_patient_services')
                <div class="col-md-4">
                    <label class="form-label" for="service_status">Статус</label>
                    <select id="service_status" class="form-select select2 service_status"
                            data-status-url="{{ route('api.patient-services.update-status', $data->id) }}"
                            data-saved-status="{{ $status->value }}">
                        @foreach ([$status, ...$status->manualNextStatuses()] as $option)
                            @php
                                [$question, $accept] = $option->isFinal()
                                    ? $confirmations[$option->value] ?? ["Перевести услугу в статус «{$option->label()}»? Изменить его потом будет нельзя.", $option->label()]
                                    : [null, null];
                            @endphp
                            <option value="{{ $option->value }}" @if ($question) data-question="{{ $question }}" data-accept="{{ $accept }}" @endif @selected($option === $status)>{{ $option->label() }}</option>
                        @endforeach
                    </select>
                </div>
            @endcan
        @endif

        @if ($data->result)
            <div class="col-12">
                <div class="datagrid-title"><i class="bx bx-file me-1" aria-hidden="true"></i>Результат</div>
                <div class="fst-italic">{{ $data->result }}</div>
            </div>
        @endif
    </div>
</div>

@foreach ($data->surveyResponses as $response)
    @include('admin.patients.history.patient_med_document', ['data' => $response])
@endforeach

@can('store_survey_response')
    <div class="text-end">
        <button type="button" class="btn btn-sm btn-outline-primary"
                data-bs-toggle="modal"
                data-bs-target="#createDocumentModal"
                data-service-id="{{ $data->id }}">
            <i class="bx bx-paperclip" aria-hidden="true"></i>Прикрепить документ
        </button>
    </div>
@endcan
