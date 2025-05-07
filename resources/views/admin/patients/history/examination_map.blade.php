<div class="border-bottom pb-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">
            <i class="bi bi-calendar2-range me-2"></i>
            Госпитализация
        </h5>
        <span class="text-muted">
            @if($data->admission_date instanceof \Carbon\Carbon)
                {{ $data->admission_date->format('d.m.Y') }}
            @else
                {{ \Carbon\Carbon::parse($data->admission_date)->format('d.m.Y') }}
            @endif
            —
            @if($data->discharge_date)
                @if($data->discharge_date instanceof \Carbon\Carbon)
                    {{ $data->discharge_date->format('d.m.Y') }}
                @else
                    {{ \Carbon\Carbon::parse($data->discharge_date)->format('d.m.Y') }}
                @endif
            @else
                по настоящее время
            @endif
        </span>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="mb-3">
                <small class="text-muted d-block mb-1">
                    <i class="bi bi-clipboard-pulse me-1"></i>
                    Диагноз при поступлении
                </small>
                <div class="fs-5">{{ $data->admission_diagnosis_description ?? '—' }}</div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-3">
                <small class="text-muted d-block mb-1">
                    <i class="bi bi-clipboard-check me-1"></i>
                    Цель обследования
                </small>
                <div class="fs-5">{{ $data->examination_purpose ?? '—' }}</div>
            </div>
        </div>

        <div class="col-12">
            <hr class="my-2">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div>
                        <small class="text-muted d-block">Лечащий врач</small>
                        <div class="fs-5">{{ $data->doctor->name ?? 'Не указан' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    // Собираем все элементы истории в одну коллекцию
    $timeline = collect();

    // Добавляем услуги
    foreach($data->services as $service) {
        $timeline->push([
            'type' => 'service',
            'date' => $service->created_at,
            'data' => $service,
            'sort_date' => $service->created_at
        ]);
    }

    // Добавляем приемы
    foreach($data->receptions as $reception) {
        $timeline->push([
            'type' => 'reception',
            'date' => $reception->created_at,
            'data' => $reception,
            'sort_date' => $reception->created_at
        ]);
    }

    // Сортируем по дате (новые сверху)
    $timeline = $timeline->sortByDesc('sort_date');
@endphp

@if($timeline->count() > 0)
    <div class="history-timeline">
        <h4 class="mb-4">История</h4>

        @foreach($timeline as $item)
            <div class="timeline-item mb-4">
                @if($item['type'] === 'service')
                    <h5 class="mb-2">Услуга</h5>
                @else
                    <h5 class="mb-2">Прием</h5>
                @endif

                <div class="timeline-content">
                    @if($item['type'] === 'service')
                        @include('admin.patients.history.service', ['data' => $item['data']])
                    @else
                        @include('admin.patients.history.reception', ['data' => $item['data']])
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="alert alert-info">
        Нет данных о посещениях
    </div>
@endif

<div class="d-flex justify-content-end">
    <a href="{{ route('maps.show', $data->id) }}"
       class="btn btn-sm btn-primary btn-outline-primary me-2">
        <i class="bx bx-show"></i>
        Просмотр
    </a>

    @if($data->is_active)
        <a href="{{ route('maps.edit', $data->id) }}"
           class="btn btn-sm btn-warning btn-outline-warning">
            <i class="bx bx-edit"></i>
            Редактировать
        </a>
    @endif
</div>
