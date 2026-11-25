@extends('admin.layouts.app')

@section('title', 'Запись на приём')

@section('header')
    <x-page-header title="Запись на приём" pretitle="Приём" />
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-6 col-lg-5">
                    <label class="form-label" for="service">Услуга</label>
                    <select id="service" class="form-select select2" data-placeholder="Выберите услугу">
                        <option value=""></option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md">
                    @include('admin.receptions.partials.legend', ['unavailable' => false])
                </div>
            </div>
        </div>

        {{-- Today's slots of the doctors who provide the chosen service, drawn by the page script. --}}
        <div class="slot-grid" id="slot-grid" aria-busy="false">
            <div class="card-body border-top">
                <x-empty-state title="Выберите услугу" text="Здесь появится сегодняшнее расписание врачей, оказывающих эту услугу." icon="bx-calendar" />
            </div>
        </div>
    </div>

    @include('admin.receptions.partials.modals')

    @php
        // What the page script needs from the server: the clinic's dates, what this account may do and
        // the endpoints (placeholders such as __STAFF__ are replaced by ids).
        $appointmentData = [
            'today' => today()->toDateString(),
            'canBook' => auth('admin')->user()->can('create_receptions'),
            'canOpenCalendar' => auth('admin')->user()->can('view_reception_doctor_calendar'),
            'canCancel' => auth('admin')->user()->can('change_receptions_status'),
            'cancelled' => \App\Enums\ReceptionStatus::Cancelled->value,
            'urls' => [
                'doctors' => route('api.staff.by-service', ['service' => '__SERVICE__'], false),
                'availability' => route('api.staff.availability-today', ['staff' => '__STAFF__'], false),
                'booked' => route('api.staff.booked-today', ['staff' => '__STAFF__'], false),
                'calendar' => route('receptions.doctor.calendar', ['staff' => '__STAFF__', 'service' => '__SERVICE__'], false),
                'patients' => route('api.patients.index', absolute: false),
                'store' => route('api.receptions.store', absolute: false),
                'show' => route('api.receptions.show', ['reception' => '__RECEPTION__'], false),
                'status' => route('api.receptions.update-status', ['reception' => '__RECEPTION__'], false),
            ],
        ];
    @endphp
    <script type="application/json" id="appointment-data">@json($appointmentData)</script>
@endsection

@section('scripts')
    @vite('resources/js/pages/reception-appointment.js')
@endsection
