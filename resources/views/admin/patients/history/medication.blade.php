{{-- A prescription of the patient card history (api.patients.history.partial). --}}
<div class="datagrid mb-3">
    @foreach ([
        'Основной медикамент' => $data->medication->name,
        'Активное вещество' => $data->medication?->composition?->active_substance_name,
        'Дозировка' => $data->dosage,
        'Разовая доза' => $data->single_dose,
        'Группа препарата' => $data->medication->group->name ?? 'Не указана',
        'Дата' => $data->date_of_end,
        'Способ приёма' => $data->method->label(),
        'Правило приёма' => $data->admission_rules->label(),
    ] as $label => $value)
        <div class="datagrid-item">
            <div class="datagrid-title">{{ $label }}</div>
            <div class="datagrid-content">{{ filled($value) ? $value : '—' }}</div>
        </div>
    @endforeach
    <div class="datagrid-item">
        <div class="datagrid-title">Статус</div>
        <div class="datagrid-content"><x-status-icon :status="$data->status" decorative /> {{ $data->status->label() }}</div>
    </div>
</div>

<div class="mb-3">
    <div class="datagrid-title">Примечания</div>
    <div class="border rounded p-3 bg-surface-secondary">{{ $data->medication_notes ?: 'Примечаний нет' }}</div>
</div>

<div class="mb-3">
    <div class="datagrid-title">Время приёма</div>
    <div class="tag-list">
        @foreach (array_filter(explode(',', (string) $data->admission_period)) as $time)
            <span class="badge bg-primary-lt">{{ $time }}</span>
        @endforeach
    </div>
</div>

<div>
    <div class="datagrid-title">Совместимые медикаменты</div>
    @if ($data->compatibleMedications->isNotEmpty())
        <div class="list-group">
            @foreach ($data->compatibleMedications as $compatible)
                <div class="list-group-item d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <div class="fw-medium">{{ $compatible->name }}</div>
                        <div class="text-secondary small">
                            Активное вещество: {{ $compatible->composition?->active_substance_name ?? '—' }} ·
                            Дозировка: {{ $compatible->pivot->dosage }}
                        </div>
                    </div>
                    <span class="badge bg-success-lt">Совместим</span>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-secondary">Совместимые медикаменты не назначены.</div>
    @endif
</div>
