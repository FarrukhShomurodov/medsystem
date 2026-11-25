{{--
    The inpatients of the nurse's department (sister_window/index and the top of sister_window/show).

    @include('admin.sister_window.partials.patients', ['patients' => $patients, 'current' => $patient->id ?? null])
--}}
@use('App\Support\Display')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Пациенты</h2>
    </div>
    <div class="table-responsive">
        <table class="table card-table table-vcenter">
            <thead>
            {{-- Only what the system records: no bed, weight, height or allergy is kept for a stay yet,
                 and an "Аллергия" column of dashes would read as "no known allergies". --}}
            <tr>
                <th>ФИО / ID</th>
                <th>Возраст</th>
                <th>Дней в стационаре</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($patients as $inpatient)
                <tr @if ($inpatient->id === $current) class="table-active" @endif>
                    <td>
                        <a href="{{ route('sister.window.show', $inpatient->id) }}" @if ($inpatient->id === $current) aria-current="page" @endif>
                            {{ $inpatient->full_name }}
                        </a>
                        <span class="text-secondary">ID{{ $inpatient->id }}</span>
                    </td>
                    <td class="text-nowrap">{{ Display::age($inpatient->birth_date) }}</td>
                    <td>{{ $inpatient->activeExaminationMap?->daysInHospital() ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="3"><x-empty-state title="Нет пациентов на стационарном лечении" icon="bx-bed" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($patients->hasPages())
        <div class="card-footer">{{ $patients->links() }}</div>
    @endif
</div>
