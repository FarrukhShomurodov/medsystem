@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Создать пациента</title>
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

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Создать</h5>
        </div>
        <div class="card-body">
            <form method="post" action="{{ route('patients.store') }}"
                  enctype="multipart/form-data">
                @csrf

                <div id="basic-data" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Основные данные</h6>
                    </div>
                    <div class="row g-3 mb-3">
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
                        <div class="col-sm-4">
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

                        <div class="col-sm-4">
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

                        <div class="col-sm-4">
                            <label class="form-label" for="files">Файлы</label>
                            <div class="input-group input-group-merge">
                                <input type="file" name="files[]" id="files"
                                       multiple
                                       value="{{ old('files') }}"
                                       class="form-control @error('files') is-invalid @enderror"
                                       placeholder="Файлы"/>
                            </div>
                            @error('files')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        {{-- new --}}
                        <div class="col-sm-6">
                            <label class="form-label" for="referral_source">Откуда услышали о нас?</label>
                            <div class="input-group input-group-merge">
                                <span id="referral_source"
                                      class="input-group-text @error('referral_source') text-danger icon-border @enderror">
                                    <i class="bx bx-file"></i>
                                </span>
                                <input type="text" id="referral_source"
                                       class="form-control @error('referral_source') is-invalid @enderror"
                                       placeholder="Откуда услышали о нас?" aria-label="Откуда услышали о нас?" name="referral_source"
                                       value="{{ old('referral_source') }}"/>
                            </div>
                            @error('referral_source')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
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
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>
    </div>
@endsection
