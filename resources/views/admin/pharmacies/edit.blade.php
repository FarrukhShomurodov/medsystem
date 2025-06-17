@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Редактировать аптеку</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('pharmacies.index') }}">Аптеки </a> /</span>Редактировать
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
            <div class="step" data-target="#license">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">2</span>
                    <span class="bs-stepper-label">Лицензия и сертификация</span>
                </button>
            </div>

            <div class="line"></div>
            <div class="step" data-target="#support">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">3</span>
                    <span class="bs-stepper-label">Службы поддержки</span>
                </button>
            </div>
        </div>

        <div class="bs-stepper-content">
            <form method="post" action="{{ route('pharmacies.update', $pharmacy->id) }}" enctype="multipart/form-data">
                @csrf
                @method('put')

                <!-- Основные данные -->
                <div id="basic-data" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Основные данные</h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-company">Название <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-company2"
                                      class="input-group-text @error('name') text-danger icon-border @enderror">
                                    <i class="bx bx-building"></i>
                                </span>
                                <input type="text" id="basic-icon-default-company"
                                       class="form-control @error('name') is-invalid @enderror" placeholder="Название"
                                       aria-label="Название" name="name" value="{{ old('name', $pharmacy->name) }}"
                                       aria-describedby="basic-icon-default-company2"/>
                            </div>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="type">Типы <span class="text-danger">*</span></label>
                            <select id="type" name="type_id"
                                    class="form-control select2 @error('type_id') is-invalid @enderror">
                                @foreach($types as $type)
                                    <option
                                        value="{{ $type->id }}" @selected(old('type_id', $pharmacy->type_id) == $type->id)>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-legal_address">Юридический адрес <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-company2"
                                      class="input-group-text @error('legal_address') text-danger icon-border @enderror">
                                    <i class="bx bx-map-pin"></i>
                                </span>
                                <input type="text" id="basic-icon-default-legal_address"
                                       class="form-control @error('legal_address') is-invalid @enderror"
                                       placeholder="Юридический адрес" aria-label="Юридический адрес"
                                       name="legal_address" value="{{ old('legal_address', $pharmacy->legal_address) }}"
                                       aria-describedby="basic-icon-default-company2"/>
                            </div>
                            @error('legal_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-actual_address">Фактический адрес <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-company2"
                                      class="input-group-text @error('actual_address') text-danger icon-border @enderror">
                                    <i class="bx bx-map"></i>
                                </span>
                                <input type="text" id="basic-icon-default-actual_address"
                                       class="form-control @error('actual_address') is-invalid @enderror"
                                       placeholder="Фактический адрес" aria-label="Фактический адрес"
                                       name="actual_address"
                                       value="{{ old('actual_address', $pharmacy->actual_address) }}"
                                       aria-describedby="basic-icon-default-company2"/>
                            </div>
                            @error('actual_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-contact_phone">Контактный телефон <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('contact_phone') text-danger icon-border @enderror">
                                <i class="bx bx-phone"></i>
                            </span>
                                <input type="text" id="basic-icon-default-contact_phone"
                                       class="form-control @error('contact_phone') is-invalid @enderror"
                                       placeholder="Контактный телефон" aria-label="Контактный телефон"
                                       name="contact_phone" value="{{ old('contact_phone', $pharmacy->contact_phone) }}"
                                       aria-describedby="basic-icon-default-company2"/>
                            </div>
                            @error('contact_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-working_hours">Режим работы <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('working_hours') text-danger icon-border @enderror">
                                <i class="bx bx-time"></i>
                            </span>
                                <input type="text" id="basic-icon-default-working_hours"
                                       class="form-control @error('working_hours') is-invalid @enderror"
                                       placeholder="Режим работы" aria-label="Режим работы" name="working_hours"
                                       value="{{ old('working_hours', $pharmacy->working_hours) }}"
                                       aria-describedby="basic-icon-default-company2"/>
                            </div>
                            @error('working_hours')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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

                <!-- Лицензия и сертификация -->
                <div id="license" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Лицензия и сертификация</h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-license_number">Номер лицензии <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('license_number') text-danger icon-border @enderror"><i
                                    class="bx bx-id-card"></i></span>
                                <input type="text" id="basic-icon-default-license_number"
                                       class="form-control @error('license_number') is-invalid @enderror"
                                       placeholder="Номер лицензии"
                                       aria-label="Номер лицензии"
                                       name="license_number"
                                       value="{{ old('license_number', $pharmacy->license->license_number) }}"
                                       aria-describedby="basic-icon-default-company2"/>
                            </div>
                            @error('license_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-license_issue_date">Дата получения лицензии <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-birth-date2"
                                      class="input-group-text @error('license_issue_date') text-danger icon-border @enderror">
                                    <i class="bx bx-calendar-check"></i>
                                </span>
                                <input type="text" name="license_issue_date"
                                       value="{{ old('license_issue_date', $pharmacy->license->license_issue_date) }}"
                                       id="basic-icon-license_issue_date"
                                       class="form-control flatpickr-date @error('license_issue_date') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD" aria-describedby="basic-icon-birth-date2" required/>
                            </div>
                            @error('license_issue_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-license_expiry_date">Срок действия лицензии <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-birth-date2"
                                      class="input-group-text @error('license_expiry_date') text-danger icon-border @enderror">
                                    <i class="bx bx-calendar-x"></i>
                                </span>
                                <input type="text" name="license_expiry_date"
                                       value="{{ old('license_expiry_date', $pharmacy->license->license_expiry_date) }}"
                                       id="basic-icon-license_expiry_date"
                                       class="form-control flatpickr-date @error('license_expiry_date') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD" aria-describedby="basic-icon-birth-date2" required/>
                            </div>
                            @error('license_expiry_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-quality_certificate_number">Номер лицензии
                                на
                                фармацевтическую деятельность <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('quality_certificate_number') text-danger icon-border @enderror"><i
                                    class="bx bx-capsule"></i></span>
                                <input type="text" id="basic-icon-default-quality_certificate_number"
                                       class="form-control @error('quality_certificate_number') is-invalid @enderror"
                                       placeholder="Номер лицензии на фармацевтическую деятельность"
                                       aria-label="Номер лицензии на фармацевтическую деятельность"
                                       name="quality_certificate_number"
                                       value="{{ old('quality_certificate_number', $pharmacy->license->quality_certificate_number) }}"
                                       aria-describedby="basic-icon-default-company2"/>
                            </div>
                            @error('quality_certificate_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-compliance_standards">Соответствие
                                нормативам и стандартам <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('compliance_standards') text-danger icon-border @enderror"><i
                                    class="bx bx-certification"></i></span>
                                <input type="text" id="basic-icon-default-compliance_standards"
                                       class="form-control @error('compliance_standards') is-invalid @enderror"
                                       placeholder="Соответствие нормативам и стандартам"
                                       aria-label="Соответствие нормативам и стандартам"
                                       name="compliance_standards"
                                       value="{{ old('compliance_standards', $pharmacy->license->compliance_standards) }}"
                                       aria-describedby="basic-icon-default-company2"/>
                            </div>
                            @error('compliance_standards')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Navigation -->
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-label-secondary btn-prev">
                                <i class="bx bx-chevron-left bx-sm ms-sm-n2"></i> Назад
                            </button>
                            <button class="btn btn-primary btn-next">
                                Далее <i class="bx bx-chevron-right bx-sm me-sm-n2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Службы поддержки и консультации -->
                <div id="support" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Службы поддержки и консультации</h6>
                    </div>
                    <div class="row g-3">

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-consultation_phone">Телефон для
                                консультаций <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('consultation_phone') text-danger icon-border @enderror"><i
                                    class="bx bx-phone"></i></span>
                                <input type="text" id="basic-icon-default-consultation_phone"
                                       class="form-control @error('consultation_phone') is-invalid @enderror"
                                       placeholder="Телефон для консультаций"
                                       aria-label="Телефон для консультаций"
                                       name="consultation_phone"
                                       value="{{ old('consultation_phone', $pharmacy->support->consultation_phone) }}"
                                       aria-describedby="basic-icon-default-company2"/>
                            </div>
                            @error('consultation_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="type">Возможность получить консультацию <span
                                    class="text-danger">*</span></label>
                            <select id="type" name="medication_consultation_available"
                                    class="form-control select2 @error('medication_consultation_available') is-invalid @enderror">
                                <option
                                    value="1" @selected(old('medication_consultation_available', $pharmacy->supportmedication_consultation_available) == '1')>
                                    Да
                                </option>

                                <option
                                    value="0" @selected(old('medication_consultation_available', $pharmacy->supportmedication_consultation_available) == '0')>
                                    Нет
                                </option>
                            </select>
                            @error('medication_consultation_available')
                            <div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Navigation -->
                        <div class="col-12 d-flex justify-content-between">
                            <button class="btn btn-primary btn-prev"><i
                                    class="bx bx-chevron-left bx-sm ms-sm-n2"></i>
                                <span class="d-sm-inline-block d-none">Назад</span>
                            </button>
                            <button type="submit" class="btn btn-warning">Редактировать</button>
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

        });
    </script>
@endsection
