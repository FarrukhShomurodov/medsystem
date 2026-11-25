@extends('admin.layouts.app')

@section('title', 'Изменить назначение')

@section('header')
    <x-page-header title="Изменить назначение" :breadcrumbs="[
        'Пациенты' => auth()->user()?->can('list_patients') ? route('patients.index') : null,
        'Карточка пациента' => auth()->user()?->can('view_patients') ? route('patients.show', $medication->patient_id) : null,
    ]" />
@endsection

@section('content')
    <div class="card">
        <form action="{{ route('patient.medication.update', $medication->id) }}" method="POST" id="prescription-form"
              data-substance-url="{{ route('api.medications.by-active-substance', ['substance' => '__substance__']) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="patient_id" value="{{ $medication->patient_id }}">

            <div class="card-body prescription-row">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label required" for="medication">Основной медикамент</label>
                        <select id="medication" name="medications[]" class="form-select medication-select" required @disabled($locked)>
                            @foreach ($allMedications as $med)
                                @php $substance = $med->composition?->active_substance_name; @endphp
                                <option value="{{ $med->id }}" data-active-substance="{{ $substance }}"
                                    @selected(old('medications.0', $medication->medication_id) == $med->id)>
                                    {{ $med->name }}@if ($substance) ({{ $substance }})@endif
                                </option>
                            @endforeach
                        </select>
                        @if ($locked)
                            {{-- A disabled select is not submitted; the drug stays the same. --}}
                            <input type="hidden" name="medications[]" value="{{ $medication->medication_id }}">
                            <div class="form-hint">Препарат уже отправлен на этаж: изменить можно только время, способ и правила приёма.</div>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required" for="dosage">Дозировка (количество единиц)</label>
                        <input type="number" min="1" id="dosage" name="dosages[]" class="form-control @error('dosages.0') is-invalid @enderror"
                               value="{{ old('dosages.0', $medication->units()) }}" placeholder="Дозировка" required @readonly($locked)>
                        @error('dosages.0')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required" for="single-dose">Разовая доза</label>
                        <input type="text" id="single-dose" name="single_doses[]" class="form-control @error('single_doses.0') is-invalid @enderror"
                               value="{{ old('single_doses.0', $medication->single_dose) }}" placeholder="Разовая доза" required maxlength="50">
                        @error('single_doses.0')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required" for="date-of-end">Дата</label>
                        <input type="text" id="date-of-end" name="date_of_ends[]" placeholder="ГГГГ-ММ-ДД"
                               class="form-control flatpickr-date @error('date_of_ends.0') is-invalid @enderror"
                               value="{{ old('date_of_ends.0', \Carbon\Carbon::parse($medication->date_of_end)->format('Y-m-d')) }}" required>
                        @error('date_of_ends.0')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- The status changes through the registrar, the cashier and the cancel button, never by editing. --}}
                    <div class="col-md-4">
                        <div class="form-label">Статус</div>
                        <div class="form-control-plaintext">
                            <x-status-icon :status="$medication->status" decorative /> {{ $medication->status->label() }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required" for="method">Способ приёма</label>
                        <select id="method" name="methods[]" class="form-select" required>
                            @foreach (\App\Enums\AdministrationRoute::cases() as $method)
                                <option value="{{ $method->value }}" @selected(old('methods.0', $medication->method->value) === $method->value)>{{ $method->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required" for="admission-rule">Правило приёма</label>
                        <select id="admission-rule" name="admission_rules[]" class="form-select" required>
                            @foreach (\App\Enums\AdmissionRule::cases() as $rule)
                                <option value="{{ $rule->value }}" @selected(old('admission_rules.0', $medication->admission_rules->value) === $rule->value)>{{ $rule->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="notes">Примечания</label>
                        {{-- PatientMedicationRequest: medication_notes.* max:255. --}}
                        <textarea id="notes" name="medication_notes[]" rows="2" placeholder="Примечания" maxlength="255"
                                  class="form-control @error('medication_notes.0') is-invalid @enderror">{{ old('medication_notes.0', $medication->medication_notes) }}</textarea>
                        @error('medication_notes.0')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        @include('admin.patients.partials.admission-times', [
                            'times' => old('admission_periods.0', is_array($medication->admission_period) ? implode(',', $medication->admission_period) : $medication->admission_period),
                        ])
                        @error('admission_periods.0')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        @include('admin.patients.partials.compatible-medications', ['compatible' => $medication->compatibleMedications])
                    </div>
                </div>
            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary"><i class="bx bx-save" aria-hidden="true"></i>Сохранить назначение</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    @vite('resources/js/pages/prescription-edit.js')
@endsection
