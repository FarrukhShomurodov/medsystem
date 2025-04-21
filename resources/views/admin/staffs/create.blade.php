@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Персонал</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('staffs.index') }}">Персонал </a> /</span>Создать
    </h6>

    <div class="alert-container position-fixed top-0 end-0 p-3" style="z-index: 10050;">
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <div class="alert alert-solid-danger alert-dismissible d-flex align-items-center" role="alert">
                    <i class="bx bx-error-circle fs-4 me-2"></i>
                    {{ $error }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endforeach
        @endif
    </div>

    <div class="bs-stepper wizard-numbered wizard-vertical vertical shadow-sm mt-2">
        <div class="bs-stepper-header">
            <div class="step" data-target="#basic-data">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">1</span>
                    <span class="bs-stepper-label">Основные данные</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#contacts">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">2</span>
                    <span class="bs-stepper-label">Контактная информация</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#employee_details">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">3</span>
                    <span class="bs-stepper-label">Проф. данные</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#licenses">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">4</span>
                    <span class="bs-stepper-label">Документы</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#schedule">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">5</span>
                    <span class="bs-stepper-label">График работы</span>
                </button>
            </div>
        </div>

        <div class="bs-stepper-content">
            <form method="post" action="{{ route('staffs.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- Основные данные -->
                <div id="basic-data" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Основные данные</h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-company">Имя <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('name') text-danger icon-border @enderror"><i
                                    class="bx bx-rename"></i></span>
                                <input type="text" id="basic-icon-default-company"
                                       class="form-control @error('name') is-invalid @enderror"
                                       placeholder="Имя" aria-label="Имя"
                                       name="name"
                                       value="{{ old('name') }}"
                                       aria-describedby="basic-icon-default-company2" required/>
                            </div>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-company">Фамилия <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('last_name') text-danger icon-border @enderror"><i
                                    class="bx bx-rename"></i></span>
                                <input type="text" id="basic-icon-default-company"
                                       class="form-control @error('last_name') is-invalid @enderror"
                                       placeholder="Фамилия" aria-label="Фамилия"
                                       name="last_name"
                                       value="{{ old('last_name') }}"
                                       aria-describedby="basic-icon-default-company2" required/>
                            </div>
                            @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-company">Отчество <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('middle_name') text-danger icon-border @enderror"><i
                                    class="bx bx-rename"></i></span>
                                <input type="text" id="basic-icon-default-company"
                                       class="form-control @error('middle_name') is-invalid @enderror"
                                       placeholder="Отчество" aria-label="Отчество"
                                       name="middle_name"
                                       value="{{ old('middle_name') }}"
                                       aria-describedby="basic-icon-default-company2" required/>
                            </div>
                            @error('middle_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-date_birthday">Дата рождение <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-birth-date2"
                                      class="input-group-text @error('date_birthday') text-danger icon-border @enderror">
                                    <i class="bx bx-calendar"></i>
                                </span>
                                <input type="text" name="date_birthday" value="{{ old('date_birthday') }}"
                                       id="basic-icon-date_birthday"
                                       class="form-control flatpickr-date @error('date_birthday') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD" aria-describedby="basic-icon-birth-date2" required/>
                            </div>
                            @error('date_birthday')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @php
                            $genderRu = [
                                'male' => 'Мужчина',
                                'female' => 'Женщина'
                            ]
                        @endphp
                        <div class="col-sm-6">
                            <label class="form-label" for="type">Пол <span
                                    class="text-danger">*</span></label>
                            <select id="type" name="gender"
                                    class="form-control select2 @error('gender') is-invalid @enderror" required>
                                @foreach(\App\Models\Staff::GENDER as $gender)
                                    <option value="{{ $gender }}" @selected(old('gender') == $gender)>
                                        {{ $genderRu[$gender] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Navigation -->
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-label-secondary btn-prev" disabled>
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i> Назад
                            </button>
                            <button class="btn btn-primary btn-next">
                                Далее <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Контактная информация -->
                <div id="contacts" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Контактная информация</h6>
                    </div>
                    <div class="row g-3">

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-phone">Телефон <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-phone2"
                                  class="input-group-text @error('phone') text-danger icon-border @enderror"><i
                                    class="bx bx-phone"></i></span>
                                <input type="text" id="basic-icon-default-phone"
                                       class="form-control phone @error('phone') is-invalid @enderror"
                                       name="phone"
                                       value="{{ old('phone') }}"
                                       required
                                       placeholder="+ 998 (XX) XXX XX XX" aria-label="+ 998 (XX) XXX XX XX"
                                       aria-describedby="basic-icon-default-phone2"/>
                            </div>

                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-email">Email <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span class="input-group-text @error('email') text-danger icon-border @enderror"><i
                                    class="bx bx-envelope"></i></span>
                                <input type="email" id="basic-icon-default-email"
                                       class="form-control @error('email') is-invalid @enderror" placeholder="Email"
                                       name="email"
                                       required
                                       value="{{ old('email') }}"
                                       aria-label="Email" aria-describedby="basic-icon-default-email2"/>
                            </div>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-address">
                                Адрес
                                <span
                                    class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('address') text-danger icon-border @enderror">
                                <i class="bx bx-map"></i>
                            </span>
                                <input type="text" id="basic-icon-default-address"
                                       class="form-control @error('address') is-invalid @enderror"
                                       placeholder="Адрес" aria-label="Адрес"
                                       name="address"
                                       required
                                       value="{{ old('address') }}"
                                       aria-describedby="basic-icon-default-company2"/>
                            </div>

                            @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        {{-- Emergency --}}
                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-email">Экстраный кантакт (ФИО) <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span
                                class="input-group-text @error('emergency_contact_name') text-danger icon-border @enderror"><i
                                    class="bx bx-envelope"></i></span>
                                <input type="text" id="basic-icon-default-emergency_contact_name"
                                       class="form-control @error('emergency_contact_name') is-invalid @enderror"
                                       placeholder="Экстраный кантакт (ФИО)"
                                       name="emergency_contact_name"
                                       required
                                       value="{{ old('emergency_contact_name') }}"
                                       aria-label="Экстраный кантакт (ФИО)"
                                       aria-describedby="basic-icon-default-emergency_contact_name"/>
                            </div>
                            @error('emergency_contact_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-emergency_contact_phone">Экстраный кантакт
                                (Телефон) <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-emergency_contact_phone"
                                  class="input-group-text @error('emergency_contact_phone') text-danger icon-border @enderror"><i
                                    class="bx bx-phone"></i></span>
                                <input type="text" id="basic-icon-default-emergency_contact_phone"
                                       class="form-control phone @error('emergency_contact_phone') is-invalid @enderror"
                                       name="emergency_contact_phone"
                                       required
                                       value="{{ old('emergency_contact_phone') }}"
                                       placeholder="+ 998 (XX) XXX XX XX" aria-label="+ 998 (XX) XXX XX XX"
                                       aria-describedby="basic-icon-default-emergency_contact_phone"/>
                            </div>

                            @error('emergency_contact_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-emergency_contact_relation">
                                Экстраный кантакт (кем приходит)
                                <span
                                    class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('emergency_contact_relation') text-danger icon-border @enderror">
                                <i class="bx bx-map"></i>
                            </span>
                                <input type="text" id="basic-icon-default-emergency_contact_relation"
                                       class="form-control @error('emergency_contact_relation') is-invalid @enderror"
                                       placeholder=" Экстраный кантакт (кем приходит)"
                                       aria-label="Экстраный кантакт (кем приходит)"
                                       name="emergency_contact_relation"
                                       required
                                       value="{{ old('emergency_contact_relation') }}"
                                       aria-describedby="basic-icon-default-company2"/>
                            </div>

                            @error('emergency_contact_relation')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Navigation -->
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev">
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i> Назад
                            </button>
                            <button class="btn btn-primary btn-next">
                                Далее <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Проф. данные -->
                <div id="employee_details" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Проф. данные <span
                                class="text-danger">*</span></h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label" for="position">Должность<span
                                    class="text-danger">*</span></label>

                            <select id="position" name="position_id"
                                    class="form-control select2 @error('position_id') is-invalid @enderror" required>
                                @foreach($positions as $position)
                                    <option
                                        value="{{ $position->id }}" @selected($position->id == old('position_id'))>
                                        {{ $position->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('position_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="speciality">Специальность<span
                                    class="text-danger">*</span></label>

                            <select id="speciality" name="speciality_id"
                                    class="form-control select2 @error('speciality_id') is-invalid @enderror" required>
                                @foreach($specialties as $specialty)
                                    <option
                                        value="{{ $specialty->id }}" @selected($specialty == old('speciality_id'))>
                                        {{ $specialty->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('speciality_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @php
                            $categoryRu = [
                                'first' => 'Первый',
                                'second' => 'Второй'
                            ]
                        @endphp
                        <div class="col-sm-4">
                            <label class="form-label" for="category">Категория<span
                                    class="text-danger">*</span></label>

                            <select id="category" name="category"
                                    class="form-control select2 @error('category') is-invalid @enderror" required>
                                @foreach(\App\Models\StaffProfessionalData::CATEGORY as $category)
                                    <option
                                        value="{{ $category }}" @selected($category == old('category'))>
                                        {{ $categoryRu[$category] }}
                                    </option>
                                @endforeach
                            </select>

                            @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="department_id">Отделения<span
                                    class="text-danger">*</span></label>

                            <select id="department_id" name="department_id"
                                    class="form-control select2 @error('department_id') is-invalid @enderror" required>
                                @foreach($departments as $department)
                                    <option
                                        value="{{ $department->id }}" @selected($department->id == old('department_id'))>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="services">Услуги<span
                                    class="text-danger">*</span></label>

                            <select id="services" name="services[]"
                                    class="form-control select2 @error('services') is-invalid @enderror" required
                                    multiple>
                                @foreach($services as $service)
                                    <option
                                        value="{{ $service->id }}" @selected(in_array($service->id, old('services', [])) )>
                                        {{ $service->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('services')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-education_name">Название Образование <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
        <span class="input-group-text @error('education_name') text-danger icon-border @enderror">
            <i class="bx bx-book"></i>
        </span>
                                <input type="text" id="basic-icon-default-education_name"
                                       class="form-control @error('education_name') is-invalid @enderror"
                                       name="education_name"
                                       value="{{ old('education_name') }}"
                                       required
                                       placeholder="Название Образование"/>
                            </div>
                            @error('education_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-education_graduation_year">Дата окончание обучения
                                <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
        <span class="input-group-text @error('education_graduation_year') text-danger icon-border @enderror">
            <i class="bx bx-calendar"></i>
        </span>
                                <input type="text" name="education_graduation_year"
                                       value="{{ old('education_graduation_year') }}"
                                       id="basic-icon-education_graduation_year"
                                       class="form-control flatpickr-date @error('education_graduation_year') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD" required/>
                            </div>
                            @error('education_graduation_year')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-certificate_name">Название сертификата
                                <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
        <span class="input-group-text @error('certificate_name') text-danger icon-border @enderror">
            <i class="bx bx-certification"></i>
        </span>
                                <input type="text" id="basic-icon-default-certificate_name"
                                       class="form-control @error('certificate_name') is-invalid @enderror"
                                       name="certificate_name"
                                       value="{{ old('certificate_name') }}"
                                       required
                                       placeholder="Название сертификата"/>
                            </div>
                            @error('certificate_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-certificate_number">Номер лицензии <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
        <span class="input-group-text @error('certificate_number') text-danger icon-border @enderror">
            <i class="bx bx-id-card"></i>
        </span>
                                <input type="text" id="basic-icon-default-certificate_number"
                                       class="form-control @error('certificate_number') is-invalid @enderror"
                                       name="certificate_number"
                                       required
                                       value="{{ old('certificate_number') }}"
                                       placeholder="Номер лицензии"/>
                            </div>
                            @error('certificate_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-certificate_issue_date">Дата выдачи <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
        <span class="input-group-text @error('certificate_issue_date') text-danger icon-border @enderror">
            <i class="bx bx-calendar"></i>
        </span>
                                <input type="text" name="certificate_issue_date"
                                       value="{{ old('certificate_issue_date') }}"
                                       id="basic-icon-certificate_issue_date"
                                       class="form-control flatpickr-date @error('certificate_issue_date') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD" required/>
                            </div>
                            @error('certificate_issue_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-certificate_validity_period">Срок действия <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span
                                    class="input-group-text @error('certificate_validity_period') text-danger icon-border @enderror">
                                    <i class="bx bx-time-five"></i>
                                </span>
                                <input type="text" name="certificate_validity_period"
                                       value="{{ old('certificate_validity_period') }}"
                                       id="basic-icon-certificate_validity_period"
                                       class="form-control flatpickr-date @error('certificate_validity_period') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD" required/>
                            </div>
                            @error('certificate_validity_period')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <!-- Navigation -->
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev">
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i> Назад
                            </button>
                            <button class="btn btn-primary btn-next">
                                Далее <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Документы -->
                <div id="licenses" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Документы</h6>
                    </div>
                    <div class="row g-3">
                        <!-- Номер трудового договора -->
                        <div class="col-sm-4">
                            <label class="form-label" for="employment_contract_number">Номер трудового договора
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-merge">
                                <span id="employment_contract_number"
                                      class="input-group-text @error('employment_contract_number') text-danger icon-border @enderror">
                                    <i class="bx bx-file"></i>
                                </span>
                                <input type="text" id="employment_contract_number"
                                       class="form-control @error('employment_contract_number') is-invalid @enderror"
                                       name="employment_contract_number" value="{{ old('employment_contract_number') }}"
                                       required placeholder="Номер трудового договора"
                                       aria-label="Номер трудового договора"
                                       aria-describedby="employment_contract_number"/>
                            </div>
                            @error('employment_contract_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Дата подписания трудового договора -->
                        <div class="col-sm-4">
                            <label class="form-label" for="employment_contract_date_of_signing">Дата подписания
                                трудового договора
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-merge">
<span id="employment_contract_date_of_signing"
      class="input-group-text @error('employment_contract_date_of_signing') text-danger icon-border @enderror">
    <i class="bx bx-calendar"></i>
</span>
                                <input type="text" name="employment_contract_date_of_signing"
                                       value="{{ old('employment_contract_date_of_signing') }}"
                                       id="employment_contract_date_of_signing"
                                       class="form-control flatpickr-date @error('employment_contract_date_of_signing') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD" aria-describedby="employment_contract_date_of_signing"
                                       required/>
                            </div>
                            @error('employment_contract_date_of_signing')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Срок действия трудового договора -->
                        <div class="col-sm-4">
                            <label class="form-label" for="employment_contract_validity_period">Срок действия трудового
                                договора
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-merge">
<span id="employment_contract_validity_period"
      class="input-group-text @error('employment_contract_validity_period') text-danger icon-border @enderror">
    <i class="bx bx-calendar"></i>
</span>
                                <input type="text" id="employment_contract_validity_period"
                                       class="form-control @error('employment_contract_validity_period') is-invalid @enderror"
                                       name="employment_contract_validity_period" required
                                       value="{{ old('employment_contract_validity_period') }}"
                                       placeholder="Срок действия трудового договора"
                                       aria-label="Срок действия трудового договора"
                                       aria-describedby="employment_contract_validity_period"/>
                            </div>
                            @error('employment_contract_validity_period')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Дата приказа об увольнении -->
                        <div class="col-sm-4">
                            <label class="form-label" for="dismissal_orders_date">Дата приказа об увольнении
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-merge">
<span id="dismissal_orders_date"
      class="input-group-text @error('dismissal_orders_date') text-danger icon-border @enderror">
    <i class="bx bx-calendar"></i>
</span>
                                <input type="text" name="dismissal_orders_date"
                                       value="{{ old('dismissal_orders_date') }}" id="dismissal_orders_date"
                                       class="form-control flatpickr-date @error('dismissal_orders_date') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD" aria-describedby="dismissal_orders_date" required/>
                            </div>
                            @error('dismissal_orders_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Номер акта приема увольнения -->
                        <div class="col-sm-4">
                            <label class="form-label" for="dismissal_reception_number">Номер акта приема увольнения
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-merge">
<span id="dismissal_reception_number"
      class="input-group-text @error('dismissal_reception_number') text-danger icon-border @enderror">
    <i class="bx bx-file"></i>
</span>
                                <input type="text" id="dismissal_reception_number"
                                       class="form-control @error('dismissal_reception_number') is-invalid @enderror"
                                       name="dismissal_reception_number" value="{{ old('dismissal_reception_number') }}"
                                       required placeholder="Номер акта приема увольнения"
                                       aria-label="Номер акта приема увольнения"
                                       aria-describedby="dismissal_reception_number"/>
                            </div>
                            @error('dismissal_reception_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Номер приказа об увольнении -->
                        <div class="col-sm-4">
                            <label class="form-label" for="dismissal_orders_number">Номер приказа об увольнении
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-merge">
<span id="dismissal_orders_number"
      class="input-group-text @error('dismissal_orders_number') text-danger icon-border @enderror">
    <i class="bx bx-file"></i>
</span>
                                <input type="text" id="dismissal_orders_number"
                                       class="form-control @error('dismissal_orders_number') is-invalid @enderror"
                                       name="dismissal_orders_number" value="{{ old('dismissal_orders_number') }}"
                                       required placeholder="Номер приказа об увольнении"
                                       aria-label="Номер приказа об увольнении"
                                       aria-describedby="dismissal_orders_number"/>
                            </div>
                            @error('dismissal_orders_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Дата увольнения -->
                        <div class="col-12">
                            <label class="form-label" for="dismissal_reception_date">Дата увольнения
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-merge">
<span id="dismissal_reception_date"
      class="input-group-text @error('dismissal_reception_date') text-danger icon-border @enderror">
    <i class="bx bx-calendar"></i>
</span>
                                <input type="text" name="dismissal_reception_date"
                                       value="{{ old('dismissal_reception_date') }}" id="dismissal_reception_date"
                                       class="form-control flatpickr-date @error('dismissal_reception_date') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD" aria-describedby="dismissal_reception_date" required/>
                            </div>
                            @error('dismissal_reception_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Серия паспорта -->
                        <div class="col-sm-4">
                            <label class="form-label" for="passport_series">Серия паспорта
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-merge">
<span id="passport_series" class="input-group-text @error('passport_series') text-danger icon-border @enderror">
    <i class="bx bx-id-card"></i>
</span>

                                <input type="text" id="passport_series"
                                       class="form-control @error('passport_series') is-invalid @enderror"
                                       name="passport_series" value="{{ old('passport_series') }}" required
                                       placeholder="Серия паспорта"
                                       aria-label="Серия паспорта" aria-describedby="passport_series"/>
                            </div>
                            @error('passport_series')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- ИНН -->
                        <div class="col-sm-4">
                            <label class="form-label" for="INN">ИНН
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-merge">
<span id="INN" class="input-group-text @error('INN') text-danger icon-border @enderror">
    <i class="bx bx-id-card"></i>
</span>
                                <input type="text" id="INN" class="form-control @error('INN') is-invalid @enderror"
                                       name="INN" value="{{ old('INN') }}"
                                       placeholder="ИНН" aria-label="ИНН" required aria-describedby="INN"/>
                            </div>
                            @error('INN')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- СНИЛС -->
                        <div class="col-sm-4">
                            <label class="form-label" for="SNILS">СНИЛС
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-merge">
   <span id="SNILS" class="input-group-text @error('SNILS') text-danger icon-border @enderror">
    <i class="bx bx-id-card"></i>
</span>
                                <input type="text" id="SNILS" class="form-control @error('SNILS') is-invalid @enderror"
                                       name="SNILS" value="{{ old('SNILS') }}"
                                       placeholder="СНИЛС" aria-label="СНИЛС" required aria-describedby="SNILS"/>
                            </div>
                            @error('SNILS')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Навигация -->
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev">
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i> Назад
                            </button>
                            <button class="btn btn-primary btn-next">
                                Далее <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- График работы -->
                <div id="schedule" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">График работы</h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="day_start">С каких дней: <span
                                    class="text-danger">*</span></label>

                            <select id="day_start" name="day_start"
                                    class="form-control select2 @error('day_start') is-invalid @enderror" required>
                                @foreach(\App\Models\StaffSchedule::DAY_START as $day)
                                    <option
                                        value="{{ $day }}" @selected($day == old('day_start'))>
                                        {{ \App\Models\StaffSchedule::getDayOfWeekLabel($day) }}
                                    </option>
                                @endforeach
                            </select>

                            @error('day_start')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="day_end">По какой день: <span
                                    class="text-danger">*</span></label>

                            <select id="day_end" name="day_end"
                                    class="form-control select2 @error('day_end') is-invalid @enderror" required>
                                @foreach(\App\Models\StaffSchedule::DAY_END as $day)
                                    <option
                                        value="{{ $day }}" @selected($day == old('day_end'))>
                                        {{ \App\Models\StaffSchedule::getDayOfWeekLabel($day) }}
                                    </option>
                                @endforeach
                            </select>

                            @error('day_end')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="start_time">Время начала смены: <span
                                    class="text-danger">*</span></label>
                            <input id="start_time" type="time" name="start_time"
                                   class="form-control @error('start_time') is-invalid @enderror" required
                                   value="{{ old('start_time') }}">
                            @error('start_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="end_time">Время окончания смены: <span
                                    class="text-danger">*</span></label>
                            <input id="end_time" type="time" name="end_time"
                                   class="form-control @error('end_time') is-invalid @enderror" required
                                   value="{{ old('end_time') }}">
                            @error('end_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="reception_start">Начало приёма: <span
                                    class="text-danger">*</span></label>
                            <input id="reception_start" type="time" name="reception_start"
                                   class="form-control @error('reception_start') is-invalid @enderror"
                                   value="{{ old('reception_start') }}" required>
                            @error('reception_start')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="reception_end">Окончание приёма: <span
                                    class="text-danger">*</span></label>
                            <input id="reception_end" type="time" name="reception_end"
                                   class="form-control @error('reception_end') is-invalid @enderror"
                                   value="{{ old('reception_end') }}" required>
                            @error('reception_end')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Навигация -->
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev">
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i> Назад
                            </button>
                            <button type="submit" class="btn btn-success">Сохранить</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            // Initialize Flatpickr
            flatpickr('.flatpickr-date', {dateFormat: 'Y-m-d'});
            flatpickr('.flatpickr-time', {enableTime: true, noCalendar: true, time_24hr: true});
        });
    </script>
@endsection
