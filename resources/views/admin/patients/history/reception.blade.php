{{-- A doctor's appointment of the patient card history (api.patients.history.partial). --}}
@use('App\Support\Display')
<div class="d-flex justify-content-between align-items-center gap-2 mb-3">
    <h3 class="mb-0"><i class="bx bx-clipboard text-info me-2" aria-hidden="true"></i>{{ Display::date($data->date) }}</h3>
    <span class="text-secondary">{{ \Carbon\Carbon::parse($data->created_at)->format('d.m.Y') }}</span>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="datagrid-title"><i class="bx bx-time-five me-1" aria-hidden="true"></i>Время приёма</div>
        <div class="fs-3">
            {{ \Carbon\Carbon::parse($data->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($data->end_time)->format('H:i') }}
        </div>
    </div>

    <div class="col-md-6">
        <div class="datagrid-title"><i class="bx bx-user-plus me-1" aria-hidden="true"></i>Ответственный</div>
        <div class="d-flex align-items-center gap-2 fw-medium">
            <span class="avatar avatar-sm rounded-circle bg-primary-lt" aria-hidden="true">{{ mb_substr($data->staff->full_name, 0, 1) }}</span>
            {{ $data->staff->full_name }}
        </div>
    </div>

    @if ($data->comment)
        <div class="col-12">
            <div class="datagrid-title"><i class="bx bx-comment-detail me-1" aria-hidden="true"></i>Комментарий к приёму</div>
            <div class="fs-3">{{ $data->comment }}</div>
        </div>
    @endif
</div>
