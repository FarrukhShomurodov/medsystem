@extends('admin.layouts.app')

@section('title', 'Календарь врача')

@section('header')
    <x-page-header :title="'Календарь: '.$staff->full_name" pretitle="Приём"
                   :breadcrumbs="auth('admin')->user()->can('view_appointment_receptions') ? ['Запись на приём' => route('receptions.appointment')] : []" />
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <dl class="datagrid mb-0">
                @foreach([
                    'Услуга' => $service->name,
                    'Длительность приёма' => $staff->slotMinutes().' мин',
                    'Должность' => $staff->professionalData?->position?->name,
                    'Специализация' => $staff->professionalData?->speciality?->name,
                ] as $label => $value)
                    <div class="datagrid-item">
                        <dt class="datagrid-title">{{ $label }}</dt>
                        <dd class="datagrid-content mb-0">{{ $value ?? '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        <div class="card-body border-top">
            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
                <button type="button" class="btn" data-week="-1"><i class="bx bx-chevron-left" aria-hidden="true"></i>Предыдущая неделя</button>
                <h2 class="h3 mb-0" aria-live="polite">Неделя: <span id="current-week"></span></h2>
                <button type="button" class="btn" data-week="1">Следующая неделя<i class="bx bx-chevron-right ms-1 me-0" aria-hidden="true"></i></button>
            </div>
            @include('admin.receptions.partials.legend', ['unavailable' => true])
        </div>

        {{-- The week's slots of the doctor, drawn by the page script. --}}
        <div class="slot-grid" id="slot-grid" aria-busy="false"></div>
    </div>

    @include('admin.receptions.partials.modals')

    @php
        // What the page script needs from the server: the clinic's dates, what this account may do and
        // the endpoints (placeholders such as __STAFF__ are replaced by ids).
        $calendarData = [
            'doctor' => [
                'id' => $staff->id,
                'name' => $staff->full_name,
            ],
            'serviceId' => $service->id,
            'admissionPeriod' => $staff->slotMinutes(),
            // The clinic's current week and day, whatever the browser's clock says.
            'weekStart' => $weekStart,
            'today' => today()->toDateString(),
            'canBook' => auth('admin')->user()->can('create_receptions'),
            'canCancel' => auth('admin')->user()->can('change_receptions_status'),
            'cancelled' => \App\Enums\ReceptionStatus::Cancelled->value,
            'urls' => [
                'week' => route('api.staff.availability-week', ['staff' => $staff->id], false),
                'patients' => route('api.patients.index', absolute: false),
                'store' => route('api.receptions.store', absolute: false),
                'show' => route('api.receptions.show', ['reception' => '__RECEPTION__'], false),
                'status' => route('api.receptions.update-status', ['reception' => '__RECEPTION__'], false),
            ],
        ];
    @endphp
    <script type="application/json" id="calendar-data">@json($calendarData)</script>
@endsection

@section('scripts')
    @vite('resources/js/pages/reception-calendar.js')
@endsection
