{{-- A diagnosis of the patient: the patient card history (api.patients.history.partial) and its list of diagnoses. --}}
@use('App\Support\Display')
<div class="border-bottom pb-3 mb-3">
    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><i class="bx bx-clipboard text-info me-2" aria-hidden="true"></i>{{ Display::date($data->date) }}</h3>
        <span class="text-secondary">{{ \Carbon\Carbon::parse($data->created_at)->format('d.m.Y') }}</span>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="datagrid-title"><i class="bx bx-time-five me-1" aria-hidden="true"></i>Время</div>
            <div class="fs-3">{{ Display::dateTime($data->created_at) }}</div>
        </div>

        @if ($data->diagnosis?->name)
            <div class="col-12">
                <div class="datagrid-title"><i class="bx bx-comment-detail me-1" aria-hidden="true"></i>Диагноз</div>
                <div class="fs-3">{{ $data->diagnosis->name }}</div>
            </div>
        @endif
    </div>
</div>
