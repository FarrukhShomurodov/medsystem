{{--
    The search form of the patient list (patients/index), also offered on the patient card.

    @include('admin.patients.partials.filters', ['departments' => $departments, 'doctors' => $staffs])

    doctors: the attending doctors to filter by, or null to leave that filter out.
--}}
<form method="GET" action="{{ route('patients.index') }}">
    <div class="row g-3 align-items-end">
        @foreach (['id' => 'ID', 'first_name' => 'Имя', 'second_name' => 'Фамилия', 'middle_name' => 'Отчество'] as $filterName => $filterLabel)
            <div class="col-sm-6 col-md-4 col-xl-2">
                <label class="form-label" for="filter-{{ $filterName }}">{{ $filterLabel }}</label>
                <input type="text" id="filter-{{ $filterName }}" name="{{ $filterName }}" value="{{ request($filterName) }}"
                       class="form-control" placeholder="{{ $filterLabel }}" @if ($filterName === 'id') inputmode="numeric" @endif>
            </div>
        @endforeach

        <div class="col-sm-6 col-md-4 col-xl-2">
            <label class="form-label" for="filter-birth-date">Дата рождения</label>
            <div class="input-group input-group-flat">
                <span class="input-group-text"><i class="bx bx-calendar" aria-hidden="true"></i></span>
                <input type="text" id="filter-birth-date" name="birth_date" value="{{ request('birth_date') }}"
                       class="form-control flatpickr-date" placeholder="ГГГГ-ММ-ДД">
            </div>
        </div>

        <div class="col-sm-6 col-md-4 col-xl-2">
            <label class="form-label" for="filter-department">Отделение</label>
            <select id="filter-department" name="department_id" class="form-select select2">
                <option value="">Все</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
        </div>

        @isset($doctors)
            <div class="col-sm-6 col-md-4 col-xl-2">
                <label class="form-label" for="filter-staff">Врач</label>
                <select id="filter-staff" name="staff_id" class="form-select select2">
                    <option value="">Все</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected(request('staff_id') == $doctor->id)>{{ $doctor->full_name }}</option>
                    @endforeach
                </select>
            </div>
        @endisset

        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><i class="bx bx-search" aria-hidden="true"></i>Найти</button>
        </div>
    </div>
</form>
