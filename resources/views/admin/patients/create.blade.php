@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Создать пациента</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('patients.index') }}">Пациенты </a> /</span>Создать
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
            <div class="step" data-target="#contact-info">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">2</span>
                    <span class="bs-stepper-label">Контактная информация</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#insurance">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">3</span>
                    <span class="bs-stepper-label">Страховка</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#medical-info">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">4</span>
                    <span class="bs-stepper-label">Медицинская информация</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#visit-history">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">6</span>
                    <span class="bs-stepper-label">История обращений</span>
                </button>
            </div>
            {{--            <div class="line"></div>--}}
            {{--            <div class="step" data-target="#other">--}}
            {{--                <button type="button" class="step-trigger">--}}
            {{--                    <span class="bs-stepper-circle">7</span>--}}
            {{--                    <span class="bs-stepper-label">Прочее</span>--}}
            {{--                </button>--}}
            {{--            </div>--}}
        </div>

        <div class="bs-stepper-content">
            <form method="post" action="{{ route('patients.store') }}"
                  enctype="multipart/form-data">
                @csrf
                <!-- Основные данные -->
                <div id="basic-data" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Основные данные</h6>
                    </div>
                    <div class="row g-3">
                        <!-- ID, имя, фамилия, отчество -->
                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-first-name">Имя <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-first-name2"
                                      class="input-group-text @error('first_name') text-danger icon-border @enderror">
                                    <i class="bx bx-user"></i>
                                </span>
                                <input type="text" id="basic-icon-first-name"
                                       class="form-control @error('first_name') is-invalid @enderror"
                                       placeholder="Имя" aria-label="Имя" name="first_name"
                                       value="{{ old('first_name') }}"
                                       aria-describedby="basic-icon-first-name2" required/>
                            </div>
                            @error('first_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-last-name">Фамилия <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-last-name2"
                                      class="input-group-text @error('last_name') text-danger icon-border @enderror">
                                    <i class="bx bx-user-circle"></i>
                                </span>
                                <input type="text" id="basic-icon-last-name"
                                       class="form-control @error('last_name') is-invalid @enderror"
                                       placeholder="Фамилия" aria-label="Фамилия" name="last_name"
                                       value="{{ old('last_name') }}" aria-describedby="basic-icon-last-name2"
                                       required/>
                            </div>
                            @error('last_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-middle-name">Отчество</label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-middle-name2"
                                      class="input-group-text @error('middle_name') text-danger icon-border @enderror">
                                    <i class="bx bx-id-card"></i>
                                </span>
                                <input type="text" id="basic-icon-middle-name"
                                       class="form-control @error('middle_name') is-invalid @enderror"
                                       placeholder="Отчество" aria-label="Отчество" name="middle_name"
                                       value="{{ old('middle_name') }}"
                                       aria-describedby="basic-icon-middle-name2"/>
                            </div>
                            @error('middle_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Дата рождения и пол -->
                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-birth-date">Дата рождения <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-birth-date2"
                                      class="input-group-text @error('birth_date') text-danger icon-border @enderror">
                                    <i class="bx bx-calendar"></i>
                                </span>
                                <input type="text" name="birth_date" value="{{ old('birth_date') }}"
                                       id="basic-icon-birth-date"
                                       class="form-control flatpickr-date @error('birth_date') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD" aria-describedby="basic-icon-birth-date2" required/>
                            </div>
                            @error('birth_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-gender">Пол <span
                                    class="text-danger">*</span></label>
                            <select class="form-control select2" id="basic-icon-gender" name="gender" required>
                                <option value="" disabled>Выберите пол</option>
                                <option value="male" @selected('male' == old('gender'))> Мужской</option>
                                <option value="female" @selected('female' == old('gender'))> Женский</option>
                            </select>
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-citizenship">Гражданство <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-passport-series2"
                                      class="input-group-text @error('citizenship') text-danger icon-border @enderror">
                                    <i class="bx bx-file"></i>
                                </span>
                                <input type="text" id="basic-icon-citizenship"
                                       class="form-control @error('citizenship') is-invalid @enderror"
                                       placeholder="Гражданство" aria-label="Гражданство" name="citizenship"
                                       value="{{ old('citizenship', 'Узбекистан') }}"
                                       aria-describedby="basic-icon-passport-series2" required/>
                            </div>
                            @error('citizenship')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Паспортные данные -->
                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-passport-series">Серия паспорта <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-passport-series2"
                                      class="input-group-text @error('passport_series') text-danger icon-border @enderror">
                                    <i class="bx bx-file"></i>
                                </span>
                                <input type="text" id="basic-icon-passport-series"
                                       class="form-control @error('passport_series') is-invalid @enderror"
                                       placeholder="Серия паспорта" aria-label="Серия паспорта" name="passport_series"
                                       value="{{ old('passport_series') }}"
                                       aria-describedby="basic-icon-passport-series2" required/>
                            </div>
                            @error('passport_series')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-passport-issue-date">Срок действия <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-passport-issue-date2"
                                      class="input-group-text @error('passport_validity_period') text-danger icon-border @enderror">
                                    <i class="bx bx-calendar"></i>
                                </span>
                                <input type="text" name="passport_validity_period" id="basic-icon-passport-issue-date"
                                       value="{{ old('passport_validity_period') }}"
                                       class="form-control flatpickr-date @error('passport_validity_period') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD" aria-describedby="basic-icon-passport-issue-date2"
                                       required/>
                            </div>
                            @error('passport_validity_period')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-label-secondary btn-prev" disabled>
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                                <span class="d-sm-inline-block d-none">Назад</span>
                            </button>
                            <button class="btn btn-primary btn-next">
                                <span class="d-sm-inline-block d-none me-sm-1">Далее</span>
                                <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Контактная информация --}}
                <div id="contact-info" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Контактная информация</h6>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-phone">Телефон <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-phone2"
                                      class="input-group-text @error('phone') text-danger icon-border @enderror">
                                    <i class="bx bx-phone"></i>
                                </span>
                                <input type="text" id="basic-icon-default-phone"
                                       class="form-control phone @error('phone') is-invalid @enderror"
                                       name="phone" value="{{ old('phone') }}" placeholder="+ 998 (XX) XXX XX XX"
                                       aria-label="+ 998 (XX) XXX XX XX"
                                       aria-describedby="basic-icon-default-phone2" required/>
                            </div>
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-phone-alt">Дополнительный телефон </label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-phone-alt2"
                                      class="input-group-text @error('additional_phone') text-danger icon-border @enderror">
                                    <i class="bx bx-phone"></i>
                                </span>
                                <input type="text" id="basic-icon-default-phone-alt"
                                       class="form-control phone @error('additional_phone') is-invalid @enderror"
                                       name="additional_phone" value="{{ old('additional_phone') }}"
                                       placeholder="+ 998 (XX) XXX XX XX" aria-label="+ 998 (XX) XXX XX XX"
                                       aria-describedby="basic-icon-default-phone-alt2"/>
                            </div>
                            @error('additional_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-email">Email</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text @error('email') text-danger icon-border @enderror">
                                    <i class="bx bx-envelope"></i>
                                </span>
                                <input type="text" id="basic-icon-default-email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       placeholder="Email" name="email" value="{{ old('email') }}" aria-label="Email"
                                       aria-describedby="basic-icon-default-email2"/>
                            </div>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Адрес проживания --}}
                    <div class="content-header mb-3 mt-3">
                        <h6 class="mb-0">Адрес проживания</h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-region">Области <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('region_id') is-invalid @enderror"
                                    id="basic-icon-default-region" name="region_id"
                                    required>
                                <option value="" disabled selected>Выберите область</option>
                                @foreach($regions as $region)
                                    <option
                                        value="{{ $region->id }}" @selected($region->id == old('region_id'))>{{ $region->name }}</option>
                                @endforeach
                            </select>
                            @error('region_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-city">Города <span
                                    class="text-danger">*</span></label>
                            <select class="form-control select2 @error('city_id') is-invalid @enderror"
                                    id="basic-icon-default-city" name="city_id" required>
                                <option value="" disabled selected>Выберите город</option>
                                @foreach($cities as $city)
                                    <option
                                        value="{{ $city->id }}" @selected($city->id == old('city_id'))>{{ $city->name }}</option>
                                @endforeach
                            </select>

                            @error('city_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-street">Улица <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                        <span id="basic-icon-default-street2"
                              class="input-group-text @error('residential_address_street') text-danger icon-border @enderror">
                            <i class="bx bx-map"></i>
                        </span>
                                <input type="text" id="basic-icon-default-street"
                                       class="form-control @error('residential_address_street') is-invalid @enderror"
                                       placeholder="Улица" aria-label="Улица" name="residential_address_street"
                                       value="{{ old('residential_address_street') }}"
                                       aria-describedby="basic-icon-default-street2" required/>
                            </div>
                            @error('residential_address_street')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-house">Дом <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-house2"
                                  class="input-group-text @error('residential_address_house') text-danger icon-border @enderror">
                                <i class="bx bx-home"></i>
                            </span>
                                <input type="text" id="basic-icon-default-house"
                                       class="form-control @error('residential_address_house') is-invalid @enderror"
                                       placeholder="Дом" aria-label="Дом" name="residential_address_house"
                                       value="{{ old('residential_address_house') }}"
                                       aria-describedby="basic-icon-default-house2" required/>
                            </div>
                            @error('residential_address_house')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-apartment">Квартира <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-apartment2"
                                  class="input-group-text @error('residential_address_apartment') text-danger icon-border @enderror">
                                <i class="bx bx-door-open"></i>
                            </span>
                                <input type="text" id="basic-icon-default-apartment"
                                       class="form-control @error('residential_address_apartment') is-invalid @enderror"
                                       placeholder="Квартира" aria-label="Квартира" name="residential_address_apartment"
                                       value="{{ old('residential_address_apartment') }}"
                                       aria-describedby="basic-icon-default-apartment2"
                                       required/>
                            </div>
                            @error('residential_address_apartment')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-postcode">Почтовый индекс</label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-postcode2"
                                  class="input-group-text @error('residential_postcode') text-danger icon-border @enderror">
                                <i class="bx bx-envelope"></i>
                            </span>
                                <input type="number" id="basic-icon-default-postcode"
                                       class="form-control @error('residential_postcode') is-invalid @enderror"
                                       placeholder="Почтовый индекс" aria-label="Почтовый индекс"
                                       name="residential_postcode"
                                       value="{{ old('residential_postcode') }}"
                                       aria-describedby="basic-icon-default-postcode2"/>
                            </div>
                            @error('residential_postcode')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Адрес регистрации (если отличается от проживания) --}}
                    <div class="content-header mb-3 mt-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Адрес регистрации (если отличается от проживания)</h6>
                        <span id="copy_address" class="btn btn-sm btn-primary">Тот же</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-region2">Области</label>
                            <select class="form-control select2 @error('registered_region_id') is-invalid @enderror"
                                    id="basic-icon-default-region2" name="registered_region_id">
                                <option value="" disabled selected>Выберите область</option>
                                @foreach($regions as $region)
                                    <option
                                        value="{{ $region->id }}" @selected($region->id == old('registered_region_id'))>{{ $region->name }}</option>
                                @endforeach
                            </select>
                            @error('registered_region_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-city2">Города</label>
                            <select class="form-control select2  @error('registered_city_id') is-invalid @enderror"
                                    id="basic-icon-default-city2" name="registered_city_id">
                                <option value="" disabled selected>Выберите город</option>
                                @foreach($cities as $city)
                                    <option
                                        value="{{ $city->id }}" @selected($region->id == old('registered_region_id'))>{{ $city->name }}</option>
                                @endforeach
                            </select>
                            @error('registered_city_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-street_res">Улица </label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-street2"
                                      class="input-group-text @error('registered_address_street') text-danger icon-border @enderror">
                                    <i class="bx bx-map"></i>
                                </span>
                                <input type="text" id="basic-icon-default-street_res"
                                       class="form-control @error('registered_address_street') is-invalid @enderror"
                                       placeholder="Улица" aria-label="Улица" name="registered_address_street"
                                       value="{{ old('registered_address_street') }}"
                                       aria-describedby="basic-icon-default-street2"/>
                            </div>
                            @error('registered_address_street')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-house_res">Дом</label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-house2"
                                  class="input-group-text @error('registered_address_house') text-danger icon-border @enderror">
                                <i class="bx bx-home"></i>
                            </span>
                                <input type="text" id="basic-icon-default-house_res"
                                       class="form-control @error('registered_address_house') is-invalid @enderror"
                                       placeholder="Дом" aria-label="Дом" name="registered_address_house"
                                       value="{{ old('registered_address_house') }}"
                                       aria-describedby="basic-icon-default-house2"/>
                            </div>
                            @error('registered_address_house')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-apartment_res">Квартира</label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-apartment"
                                  class="input-group-text @error('registered_address_apartment') text-danger icon-border @enderror">
                                <i class="bx bx-door-open"></i>
                            </span>
                                <input type="text" id="basic-icon-default-apartment_res"
                                       class="form-control @error('registered_address_apartment') is-invalid @enderror"
                                       placeholder="Квартира" aria-label="Квартира" name="registered_address_apartment"
                                       value="{{ old('registered_address_apartment') }}"
                                       aria-describedby="basic-icon-default-apartment2"/>
                            </div>
                            @error('registered_address_apartment')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-postcode_res">Почтовый индекс</label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-postcode2"
                                  class="input-group-text @error('registered_postcode') text-danger icon-border @enderror">
                                <i class="bx bx-envelope"></i>
                            </span>
                                <input type="number" id="basic-icon-default-postcode_res"
                                       class="form-control @error('registered_postcode') is-invalid @enderror"
                                       placeholder="Почтовый индекс" aria-label="Почтовый индекс"
                                       name="registered_postcode"
                                       value="{{ old('registered_postcode') }}"
                                       aria-describedby="basic-icon-default-postcode2"/>
                            </div>
                            @error('registered_postcode')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    {{-- Экстренные контакты --}}
                    <div class="content-header mb-3 mt-3">
                        <h6 class="mb-0">Экстренные контакты</h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-first-name">ФИО контактного лица <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-first-name2"
                                      class="input-group-text @error('emergency_contact_full_name') text-danger icon-border @enderror">
                                    <i class="bx bx-user"></i>
                                </span>
                                <input type="text" id="basic-icon-default-first-name"
                                       class="form-control @error('emergency_contact_full_name') is-invalid @enderror"
                                       placeholder="ФИО контактного лица" aria-label="ФИО контактного лица"
                                       name="emergency_contact_full_name"
                                       value="{{ old('emergency_contact_full_name') }}"
                                       aria-describedby="basic-icon-default-first-name2" required/>
                            </div>
                            @error('emergency_contact_full_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-last-name">Связь с пациентом (родственник,
                                друг...) <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-last-name2"
                                      class="input-group-text @error('emergency_contact_relation') text-danger icon-border @enderror">
                                    <i class="bx bx-user-circle"></i>
                                </span>
                                <input type="text" id="basic-icon-default-last-name"
                                       class="form-control @error('emergency_contact_relation') is-invalid @enderror"
                                       placeholder="Связь с пациентом (родственник, друг...)"
                                       aria-label="Связь с пациентом (родственник, друг...)"
                                       name="emergency_contact_relation"
                                       value="{{ old('emergency_contact_relation') }}"
                                       aria-describedby="basic-icon-default-last-name2"
                                       required/>
                            </div>
                            @error('emergency_contact_relation')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-phone_emerge">Телефон <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-phone2"
                                      class="input-group-text @error('emergency_contact_phone') text-danger icon-border @enderror">
                                    <i class="bx bx-phone"></i>
                                </span>
                                <input type="text" id="basic-icon-default-phone_emerge"
                                       class="form-control phone @error('emergency_contact_phone') is-invalid @enderror"
                                       name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}"
                                       placeholder="+ 998 (XX) XXX XX XX"
                                       aria-label="+ 998 (XX) XXX XX XX" aria-describedby="basic-icon-default-phone2"
                                       required/>
                            </div>
                            @error('emergency_contact_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-address">Адрес</label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-address2"
                                      class="input-group-text @error('emergency_contact_address') text-danger icon-border @enderror">
                                    <i class="bx bx-map"></i>
                                </span>
                                <input type="text" id="basic-icon-default-address"
                                       class="form-control @error('emergency_contact_address') is-invalid @enderror"
                                       placeholder="Адрес" aria-label="Адрес" name="emergency_contact_address"
                                       value="{{ old('emergency_contact_address') }}"
                                       aria-describedby="basic-icon-default-address2"/>
                            </div>
                            @error('emergency_contact_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev">
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                                <span class="d-sm-inline-block d-none">Назад</span>
                            </button>
                            <button class="btn btn-primary btn-next"><span class="d-sm-inline-block d-none">Далее</span>
                                <i
                                    class="bx bx-chevron-right bx-sm me-sm-n2"></i></button>
                        </div>

                    </div>
                </div>

                {{-- Страховка--}}
                <div id="insurance" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Страховка</h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-policy-number">Номер полиса</label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-policy-number2"
                                      class="input-group-text @error('insurance_policy_number') text-danger icon-border @enderror">
                                    <i class="bx bx-file"></i>
                                </span>
                                <input type="text" id="basic-icon-default-policy-number"
                                       class="form-control @error('insurance_policy_number') is-invalid @enderror"
                                       placeholder="Номер полиса" name="insurance_policy_number"
                                       value="{{ old('insurance_policy_number') }}" aria-label="Номер полиса"
                                       aria-describedby="basic-icon-default-policy-number2"/>
                            </div>
                            @error('insurance_policy_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-insurance-company">Компания
                                страхователя</label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-insurance-company2"
                                      class="input-group-text @error('insurance_company') text-danger icon-border @enderror">
                                    <i class="bx bx-building"></i>
                                </span>
                                <input type="text" id="basic-icon-default-insurance-company"
                                       class="form-control @error('insurance_company') is-invalid @enderror"
                                       placeholder="Компания страхователя" name="insurance_company"
                                       value="{{ old('insurance_company') }}" aria-label="Компания страхователя"
                                       aria-describedby="basic-icon-default-insurance-company2"/>
                            </div>
                            @error('insurance_company')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-insurance-type">Тип страхования</label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-insurance-type2"
                                      class="input-group-text @error('insurance_type') text-danger icon-border @enderror">
                                    <i class="bx bx-shield"></i>
                                </span>
                                <input type="text" id="basic-icon-default-insurance-type"
                                       class="form-control @error('insurance_type') is-invalid @enderror"
                                       placeholder="Тип страхования" name="insurance_type"
                                       value="{{ old('insurance_type') }}" aria-label="Тип страхования"
                                       aria-describedby="basic-icon-default-insurance-type2"/>
                            </div>
                            @error('insurance_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="">
                            <label class="form-label" for="flatpickr-insurance-date">Срок действия полиса</label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-insurance-date2"
                                      class="input-group-text @error('insurance_expiry_date') text-danger icon-border @enderror">
                                    <i class="bx bx-calendar"></i>
                                </span>
                                <input type="text" id="flatpickr-insurance-date" name="insurance_expiry_date"
                                       value="{{ old('insurance_expiry_date') }}"
                                       class="form-control flatpickr-date @error('insurance_expiry_date') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD"
                                       aria-describedby="basic-icon-default-insurance-date2"/>
                            </div>
                            @error('insurance_expiry_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev">
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                                <span class="d-sm-inline-block d-none">Назад</span>
                            </button>
                            <button class="btn btn-primary btn-next">
                                <span class="d-sm-inline-block d-none">Далее</span>
                                <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Медицинская информация --}}
                <div id="medical-info" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Медицинская информация</h6>
                    </div>
                    <div class="row g-3">
                        <div>
                            <label class="form-label" for="basic-icon-default-history">История заболеваний <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-history2"
                                      class="input-group-text @error('medical_history') text-danger icon-border @enderror">
                                    <i class="bx bx-notepad"></i>
                                </span>
                                <textarea id="basic-icon-default-history"
                                          class="form-control @error('medical_history') is-invalid @enderror"
                                          placeholder="История заболеваний" aria-label="История заболеваний"
                                          name="medical_history"
                                          required
                                          aria-describedby="basic-icon-default-history2">{{ old('medical_history') }}</textarea>
                            </div>
                            @error('medical_history')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-allergies">Аллергии <span
                                    class="text-danger">*</span></label>
                            <select id="basic-icon-default-allergies" name="allegories[]"
                                    class="form-control select2 @error('allegories') is-invalid @enderror"
                                    multiple required>
                                <option value="" disabled>Выберите аллергию</option>
                                @foreach($allegories as $allegory)
                                    <option
                                        value="{{ $allegory->id }}" @selected(in_array($allegory->id, old('allegories', [])))>{{ $allegory->name }}</option>
                                @endforeach
                            </select>
                            @error('allegories')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-chronic-diseases">Хронические болезни
                                <span class="text-danger">*</span></label>
                            <select id="basic-icon-default-chronic-diseases" name="chronic_diseases[]"
                                    class="form-control select2 @error('chronic_diseases') is-invalid @enderror"
                                    multiple
                                    required>
                                <option value="" disabled>Выберите хроническию болезнь</option>
                                @foreach($diseases as $disease)
                                    <option
                                        value="{{ $disease->id }}" @selected(in_array($disease->id, old('chronic_diseases', [])))>{{ $disease->name }}</option>
                                @endforeach
                            </select>
                            @error('chronic_diseases')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-blood-type">Группа крови <span
                                    class="text-danger">*</span></label>
                            <select id="basic-icon-default-blood-type" name="blood_type"
                                    class="form-control select2 @error('blood_type') is-invalid @enderror"
                                    required>
                                <option value="" disabled>Выберите группу крови</option>
                                @foreach (App\Models\Patient::BLOOD_TYPES as $bloodType)
                                    <option
                                        value="{{ $bloodType }}" @selected($bloodType == old('blood_type'))>{{ $bloodType }}</option>
                                @endforeach
                            </select>
                            @error('blood_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-rh-factor">Резус-фактор</label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-rh-factor2"
                                      class="input-group-text @error('rh_factor') text-danger icon-border @enderror">
                                    <i class="bx bxs-donate-blood"></i>
                                </span>
                                <input type="number" id="basic-icon-default-rh-factor"
                                       class="form-control @error('rh_factor') is-invalid @enderror"
                                       placeholder="Резус-фактор" aria-label="Резус-фактор" name="rh_factor"
                                       value="{{ old('rh_factor') }}"
                                       aria-describedby="basic-icon-default-rh-factor2"/>
                            </div>
                            @error('rh_factor')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-medical-devices">Медицинские
                                устройства<span class="text-danger">*</span></label>
                            <select id="basic-icon-default-medical-devices" name="medical_devices[]"
                                    class="form-control select2 @error('medical_devices') is-invalid @enderror"
                                    required>
                                <option value="" disabled>Выберите устройство</option>
                                @foreach($devices as $device)
                                    <option
                                        value="{{ $device->id }}" @selected(in_array($device->id, old('medical_devices', [])))>{{ $device->name }}</option>
                                @endforeach
                            </select>
                            @error('medical_devices')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label" for="basic-icon-default-special-instructions">Особые
                                указания</label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-special-instructions2"
                                      class="input-group-text @error('special_instructions') text-danger icon-border @enderror">
                                    <i class="bx bx-detail"></i>
                                </span>
                                <textarea id="basic-icon-default-special-instructions"
                                          class="form-control @error('special_instructions') is-invalid @enderror"
                                          placeholder="Особые указания" aria-label="Особые указания"
                                          name="special_instructions"
                                          aria-describedby="basic-icon-default-special-instructions2">{{ old('special_instructions') }}</textarea>
                            </div>
                            @error('special_instructions')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev">
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                                <span class="d-sm-inline-block d-none">Назад</span>
                            </button>
                            <button class="btn btn-primary btn-next">
                                <span class="d-sm-inline-block d-none">Далее</span>
                                <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- История обращений --}}
                <div id="visit-history" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-3">История обращений</h6>
                        <p>Кол-во посещений: 0 </p>
                    </div>
                    <div class="row g-3">
                        <div>
                            <label class="form-label" for="basic-icon-default-visit-dates">Дата посещения <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <input type="date" id="basic-icon-default-visit-dates"
                                       class="form-control flatpickr-date @error('admission_date') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD" name="admission_date"
                                       value="{{ old('admission_date') }}"
                                       required
                                       aria-describedby="basic-icon-default-visit-dates2"/>
                            </div>
                            @error('admission_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-doctors">Лечащий врач<span
                                    class="text-danger">*</span></label>
                            <select id="basic-icon-default-doctors" name="doctor_id"
                                    class="form-control select2 @error('doctor_id') is-invalid @enderror"
                                    multiple required>
                                <option value="" disabled>Выберите врача</option>
                                @foreach($doctors as $doctor)
                                    <option
                                        value="{{ $doctor->id }}" @selected($doctor->id == old('doctor_id'))>{{ $doctor->name }}</option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-departments">Отделение, к которому
                                прикреплен<span class="text-danger">*</span></label>
                            <select id="basic-icon-default-departments" name="institution_id"
                                    class="form-control select2 @error('institution_id') is-invalid @enderror" multiple
                                    required>
                                <option value="" disabled>Выберите отделение</option>
                                @foreach($institutions as $institution)
                                    <option
                                        value="{{ $institution->id }}" @selected($institution->id == old('institution_id'))>{{ $institution->name }}</option>
                                @endforeach
                            </select>
                            @error('institution_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev"><i
                                    class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                                <span class="d-sm-inline-block d-none">Назад</span>
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
            $('#copy_address').on('click', function () {
                // Копируем значения из обычных inputов
                $('#basic-icon-default-street_res').val($('#basic-icon-default-street').val());
                $('#basic-icon-default-house_res').val($('#basic-icon-default-house').val());
                $('#basic-icon-default-apartment_res').val($('#basic-icon-default-apartment').val());
                $('#basic-icon-default-postcode_res').val($('#basic-icon-default-postcode').val());

                // Копируем значения из select'ов с Select2
                const regionVal = $('#basic-icon-default-region').val();
                const cityVal = $('#basic-icon-default-city').val();

                $('#basic-icon-default-region2').val(regionVal).trigger('change');
                $('#basic-icon-default-city2').val(cityVal).trigger('change');
            });
        })
    </script>
@endsection
