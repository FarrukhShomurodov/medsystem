{{--
    A patient with the stay the patient lists show, the latest one (latestExaminationMap, loaded with
    its department and its doctor's three names). The columns are those of list-head.

    @include('admin.patients.partials.list-row', ['patient' => $patient, 'number' => $patient->id, 'current' => false])

    number:  the first column (the patient's id on the list, the position among the recent patients)
    current: the patient whose card is open, highlighted
--}}
@use('App\Support\Display')
@php
    $map = $patient->latestExaminationMap;
@endphp
<tr @if ($current ?? false) class="table-active" aria-current="true" @endif>
    <td class="text-secondary">{{ $number }}</td>
    {{-- The day the stay began, not the day the patient was registered. --}}
    <td class="text-nowrap">{{ Display::date($map?->admission_date) }}</td>
    <td>{{ $map?->department?->name ?? '—' }}</td>
    <td>
        <a href="{{ route('patients.show', $patient->id) }}">{{ $patient->full_name }}</a>
    </td>
    <td class="text-nowrap">{{ Display::date($patient->birth_date) }} / {{ Display::age($patient->birth_date) }}</td>
    <td>{{ $map?->admission_diagnosis_description ?? '—' }}</td>
    <td>{{ $map?->doctor?->full_name ?? '—' }}</td>
    <td>{{ $map?->daysInHospital() ?? '—' }}</td>
    <td>
        @can('edit_patients')
            <div class="table-actions">
                <a href="{{ route('patients.edit', $patient->id) }}" class="btn btn-sm">Изменить</a>
            </div>
        @endcan
    </td>
</tr>
