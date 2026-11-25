{{-- The pharmacy wizard of the create and edit pages. Expects $pharmacy (null on create) and $types. --}}
@php
    $license = $pharmacy?->license;
    $support = $pharmacy?->support;

    // "1" / "0" for the select, whether it comes back from the form or from the boolean column.
    $consultation = old('medication_consultation_available', $support?->medication_consultation_available);
    $consultation = $consultation === null ? null : (filter_var($consultation, FILTER_VALIDATE_BOOLEAN) ? '1' : '0');
@endphp

<div class="bs-stepper wizard-vertical vertical">
    <div class="bs-stepper-header" role="tablist">
        <div class="step" data-target="#basic-data">
            <button type="button" class="step-trigger" role="tab" id="basic-data-trigger" aria-controls="basic-data">
                <span class="bs-stepper-circle">1</span>
                <span class="bs-stepper-label">Основные данные</span>
            </button>
        </div>
        <div class="step" data-target="#license">
            <button type="button" class="step-trigger" role="tab" id="license-trigger" aria-controls="license">
                <span class="bs-stepper-circle">2</span>
                <span class="bs-stepper-label">Лицензия и сертификация</span>
            </button>
        </div>
        <div class="step" data-target="#support">
            <button type="button" class="step-trigger" role="tab" id="support-trigger" aria-controls="support">
                <span class="bs-stepper-circle">3</span>
                <span class="bs-stepper-label">Службы поддержки</span>
            </button>
        </div>
    </div>

    <div class="bs-stepper-content">
        <form method="POST" enctype="multipart/form-data"
              action="{{ $pharmacy ? route('pharmacies.update', $pharmacy->id) : route('pharmacies.store') }}">
            @csrf
            @if ($pharmacy)
                @method('PUT')
            @endif

            <div id="basic-data" class="content" role="tabpanel" aria-labelledby="basic-data-trigger">
                <div class="content-header mb-3">
                    <h2 class="h3 mb-0">Основные данные</h2>
                </div>
                <div class="row g-3">
                    <div class="col-md-6 col-xl-4">
                        <label class="form-label required" for="name">Название</label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text"><i class="bx bx-building" aria-hidden="true"></i></span>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $pharmacy?->name) }}">
                        </div>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 col-xl-4">
                        <label class="form-label required" for="type_id">Тип</label>
                        <select id="type_id" name="type_id" class="form-select select2 @error('type_id') is-invalid @enderror">
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}" @selected(old('type_id', $pharmacy?->type_id) == $type->id)>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('type_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 col-xl-4">
                        <label class="form-label required" for="legal_address">Юридический адрес</label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text"><i class="bx bx-map-pin" aria-hidden="true"></i></span>
                            <input type="text" id="legal_address" name="legal_address"
                                   class="form-control @error('legal_address') is-invalid @enderror"
                                   value="{{ old('legal_address', $pharmacy?->legal_address) }}">
                        </div>
                        @error('legal_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 col-xl-4">
                        <label class="form-label required" for="actual_address">Фактический адрес</label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text"><i class="bx bx-map" aria-hidden="true"></i></span>
                            <input type="text" id="actual_address" name="actual_address"
                                   class="form-control @error('actual_address') is-invalid @enderror"
                                   value="{{ old('actual_address', $pharmacy?->actual_address) }}">
                        </div>
                        @error('actual_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 col-xl-4">
                        <label class="form-label required" for="contact_phone">Контактный телефон</label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text"><i class="bx bx-phone" aria-hidden="true"></i></span>
                            <input type="tel" id="contact_phone" name="contact_phone" autocomplete="tel"
                                   class="form-control @error('contact_phone') is-invalid @enderror"
                                   value="{{ old('contact_phone', $pharmacy?->contact_phone) }}">
                        </div>
                        @error('contact_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 col-xl-4">
                        <label class="form-label required" for="working_hours">Режим работы</label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text"><i class="bx bx-time" aria-hidden="true"></i></span>
                            <input type="text" id="working_hours" name="working_hours"
                                   class="form-control @error('working_hours') is-invalid @enderror"
                                   value="{{ old('working_hours', $pharmacy?->working_hours) }}">
                        </div>
                        @error('working_hours')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 d-flex">
                        <button class="btn btn-primary btn-next ms-auto">Далее<i class="bx bx-chevron-right ms-2 me-0" aria-hidden="true"></i></button>
                    </div>
                </div>
            </div>

            <div id="license" class="content" role="tabpanel" aria-labelledby="license-trigger">
                <div class="content-header mb-3">
                    <h2 class="h3 mb-0">Лицензия и сертификация</h2>
                </div>
                <div class="row g-3">
                    <div class="col-md-6 col-xl-4">
                        <label class="form-label required" for="license_number">Номер лицензии</label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text"><i class="bx bx-id-card" aria-hidden="true"></i></span>
                            <input type="text" id="license_number" name="license_number"
                                   class="form-control @error('license_number') is-invalid @enderror"
                                   value="{{ old('license_number', $license?->license_number) }}">
                        </div>
                        @error('license_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 col-xl-4">
                        <label class="form-label required" for="license_issue_date">Дата получения лицензии</label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text"><i class="bx bx-calendar-check" aria-hidden="true"></i></span>
                            <input type="text" id="license_issue_date" name="license_issue_date" placeholder="ГГГГ-ММ-ДД"
                                   class="form-control flatpickr-date @error('license_issue_date') is-invalid @enderror"
                                   value="{{ old('license_issue_date', $license?->license_issue_date) }}" required>
                        </div>
                        @error('license_issue_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 col-xl-4">
                        <label class="form-label required" for="license_expiry_date">Срок действия лицензии</label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text"><i class="bx bx-calendar-x" aria-hidden="true"></i></span>
                            <input type="text" id="license_expiry_date" name="license_expiry_date" placeholder="ГГГГ-ММ-ДД"
                                   class="form-control flatpickr-date @error('license_expiry_date') is-invalid @enderror"
                                   value="{{ old('license_expiry_date', $license?->license_expiry_date) }}" required>
                        </div>
                        @error('license_expiry_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="quality_certificate_number">Номер лицензии на фармацевтическую деятельность</label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text"><i class="bx bx-capsule" aria-hidden="true"></i></span>
                            <input type="text" id="quality_certificate_number" name="quality_certificate_number"
                                   class="form-control @error('quality_certificate_number') is-invalid @enderror"
                                   value="{{ old('quality_certificate_number', $license?->quality_certificate_number) }}">
                        </div>
                        @error('quality_certificate_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="compliance_standards">Соответствие нормативам и стандартам</label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text"><i class="bx bx-certification" aria-hidden="true"></i></span>
                            <input type="text" id="compliance_standards" name="compliance_standards"
                                   class="form-control @error('compliance_standards') is-invalid @enderror"
                                   value="{{ old('compliance_standards', $license?->compliance_standards) }}">
                        </div>
                        @error('compliance_standards')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 d-flex justify-content-between">
                        <button class="btn btn-prev"><i class="bx bx-chevron-left" aria-hidden="true"></i>Назад</button>
                        <button class="btn btn-primary btn-next">Далее<i class="bx bx-chevron-right ms-2 me-0" aria-hidden="true"></i></button>
                    </div>
                </div>
            </div>

            <div id="support" class="content" role="tabpanel" aria-labelledby="support-trigger">
                <div class="content-header mb-3">
                    <h2 class="h3 mb-0">Службы поддержки и консультации</h2>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required" for="consultation_phone">Телефон для консультаций</label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text"><i class="bx bx-phone" aria-hidden="true"></i></span>
                            <input type="tel" id="consultation_phone" name="consultation_phone"
                                   class="form-control @error('consultation_phone') is-invalid @enderror"
                                   value="{{ old('consultation_phone', $support?->consultation_phone) }}">
                        </div>
                        @error('consultation_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required" for="medication_consultation_available">Возможность получить консультацию</label>
                        <select id="medication_consultation_available" name="medication_consultation_available"
                                class="form-select @error('medication_consultation_available') is-invalid @enderror">
                            <option value="1" @selected($consultation === '1')>Да</option>
                            <option value="0" @selected($consultation === '0')>Нет</option>
                        </select>
                        @error('medication_consultation_available')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 d-flex justify-content-between">
                        <button class="btn btn-prev"><i class="bx bx-chevron-left" aria-hidden="true"></i>Назад</button>
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
