{{--
    The latest admissions: the open stays, the latest first, each linking to the patient's card.

    admissions: App\Models\ExaminationMap with patient, department and doctor
--}}
@use('App\Queries\Dashboard\DashboardCard')
@use('Illuminate\Support\Carbon')

<div class="card" data-dashboard-card="{{ DashboardCard::Admissions->value }}">
    <div class="card-header">
        <h2 class="card-title">Последние госпитализации</h2>
        @can('list_patients')
            <div class="card-actions">
                <a href="{{ route('patients.index') }}" class="btn btn-sm">Все пациенты</a>
            </div>
        @endcan
    </div>

    @if ($admissions->isEmpty())
        <x-empty-state title="Сейчас в стационаре никого нет" icon="bx-bed"
                       text="Открытые карты обследования появятся здесь." />
    @else
        <div class="list-group list-group-flush">
            @foreach ($admissions as $map)
                <div class="list-group-item">
                    <div class="row align-items-center g-2">
                        <div class="col dashboard-shrink">
                            @if ($map->patient !== null)
                                <a href="{{ route('patients.show', $map->patient) }}" class="d-block text-truncate">{{ $map->patient->full_name }}</a>
                            @else
                                <span class="d-block">—</span>
                            @endif
                            <div class="text-secondary small text-truncate">
                                {{ $map->department->name ?? 'Отделение не указано' }}
                                · {{ $map->doctor?->full_name ?? 'врач не указан' }}
                                @if ($map->is_outpatient)
                                    · амбулаторно
                                @endif
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <div class="small text-secondary">Поступление</div>
                            <div class="dashboard-figure">{{ Carbon::parse($map->admission_date)->format('d.m.Y') }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
