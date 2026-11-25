{{--
    The day's appointments: how many in each status, then the first booked ones in the order of the day
    (time, patient, doctor, status; on a phone the doctor goes under the patient).

    receptions:   App\Queries\Dashboard\ReceptionsToday
    appointments: the first of them (App\Models\Reception with patient and staff)
    day:          the day shown (Y-m-d): the links open the reception list on it
--}}
@use('App\Queries\Dashboard\DashboardCard')
@php($opensPatients = Gate::check('view_patients'))

<div class="card" data-dashboard-card="{{ DashboardCard::Appointments->value }}">
    <div class="card-header">
        <h2 class="card-title">{{ $receptions->ownOnly ? 'Мои приёмы на сегодня' : 'Приёмы на сегодня' }}</h2>
        <div class="card-actions">
            <a href="{{ route('receptions.index', ['date' => $day]) }}" class="btn btn-sm">Все приёмы</a>
        </div>
    </div>

    @if ($receptions->statuses() !== [])
        <div class="card-body border-bottom py-2">
            <ul class="list-inline mb-0 dashboard-statuses" aria-label="Приёмы по статусам">
                @foreach ($receptions->statuses() as $status)
                    <li class="list-inline-item">
                        <a href="{{ route('receptions.index', ['date' => $day, 'status' => $status->value]) }}" class="text-reset text-decoration-none">
                            <x-status-badge :status="$status" />
                            <span class="fw-medium dashboard-figure">{{ $receptions->count($status) }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($appointments->isEmpty())
        <x-empty-state title="На сегодня приёмов нет" icon="bx-calendar"
                       text="Записанные на сегодня приёмы появятся здесь." />
    @else
        <div class="list-group list-group-flush">
            @foreach ($appointments as $appointment)
                <div class="list-group-item">
                    <div class="row align-items-center g-2">
                        <div class="col-auto text-secondary dashboard-figure">
                            {{ substr($appointment->start_time, 0, 5) }}<span class="d-none d-sm-inline">–{{ substr($appointment->end_time, 0, 5) }}</span>
                        </div>
                        <div class="col dashboard-shrink">
                            @if ($appointment->patient === null)
                                <span class="d-block">—</span>
                            @elseif ($opensPatients)
                                <a href="{{ route('patients.show', $appointment->patient) }}" class="d-block text-truncate">{{ $appointment->patient->full_name }}</a>
                            @else
                                <span class="d-block text-truncate">{{ $appointment->patient->full_name }}</span>
                            @endif
                            @unless ($receptions->ownOnly)
                                <div class="text-secondary small text-truncate d-md-none">{{ $appointment->staff?->full_name ?? '—' }}</div>
                            @endunless
                        </div>
                        @unless ($receptions->ownOnly)
                            <div class="col-md-4 d-none d-md-block text-secondary text-truncate dashboard-shrink">{{ $appointment->staff?->full_name ?? '—' }}</div>
                        @endunless
                        <div class="col-auto col-md-2 text-end"><x-status-badge :status="$appointment->status" /></div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($receptions->booked() > $appointments->count())
            <div class="card-footer text-secondary">
                Показаны первые {{ $appointments->count() }} из {{ $receptions->booked() }}.
                <a href="{{ route('receptions.index', ['date' => $day]) }}">Открыть список приёмов</a>
            </div>
        @endif
    @endif
</div>
