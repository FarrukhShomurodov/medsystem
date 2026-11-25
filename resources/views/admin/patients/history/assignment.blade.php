{{-- A referral of the patient card history (api.patients.history.partial). --}}
<div class="d-flex justify-content-between align-items-start gap-2 mb-2">
    <div>
        <h3 class="mb-1">
            <i class="bx bx-calendar-plus text-secondary me-2" aria-hidden="true"></i>{{ $data->service->name }}
        </h3>
        <div class="text-secondary small">
            <i class="bx bx-user-circle me-1" aria-hidden="true"></i>Назначил: {{ $data->staff?->full_name ?? 'Главный админ' }}
        </div>
    </div>
    <span class="badge bg-secondary-lt text-nowrap">
        <i class="bx bx-history me-1" aria-hidden="true"></i>{{ $data->created_at->format('d.m.Y H:i') }}
    </span>
</div>

@if ($data->comment)
    <div class="d-flex align-items-start gap-2 mt-3 pt-2 border-top">
        <i class="bx bx-message-square-detail text-secondary" aria-hidden="true"></i>
        <div class="flex-grow-1">
            <div class="text-secondary small">Комментарий:</div>
            <div class="fst-italic text-break">{{ $data->comment }}</div>
        </div>
    </div>
@endif
