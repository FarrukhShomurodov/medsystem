{{-- A hospital stay of the patient card history (api.patients.history.partial): its admission and discharge rows. --}}
@php
    $date = fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('d.m.Y') : null;
@endphp
<div class="border-bottom pb-3 mb-3">
    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0"><i class="bx bx-calendar-event me-2" aria-hidden="true"></i>Госпитализация</h3>
        <span class="text-secondary">
            {{ $date($data->admission_date) }} — {{ $date($data->discharge_date) ?? 'по настоящее время' }}
        </span>
    </div>

    <div class="datagrid">
        <div class="datagrid-item">
            <div class="datagrid-title"><i class="bx bx-pulse me-1" aria-hidden="true"></i>Диагноз при поступлении</div>
            <div class="datagrid-content">{{ $data->admission_diagnosis_description ?? '—' }}</div>
        </div>
        <div class="datagrid-item">
            <div class="datagrid-title"><i class="bx bx-task me-1" aria-hidden="true"></i>Цель обследования</div>
            <div class="datagrid-content">{{ $data->examination_purpose ?? '—' }}</div>
        </div>
        <div class="datagrid-item">
            <div class="datagrid-title">Лечащий врач</div>
            <div class="datagrid-content">{{ $data->doctor?->full_name ?? 'Не указан' }}</div>
        </div>
    </div>
</div>

<div class="btn-list justify-content-end">
    <a href="{{ route('maps.show', $data->id) }}" class="btn btn-sm">
        <i class="bx bx-show" aria-hidden="true"></i>Просмотр
    </a>

    @if ($data->is_active)
        @can('edit_maps')
            <a href="{{ route('maps.edit', $data->id) }}" class="btn btn-sm btn-primary">
                <i class="bx bx-edit" aria-hidden="true"></i>Изменить
            </a>
        @endcan
    @endif
</div>
