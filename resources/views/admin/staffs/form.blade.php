{{--
    The employee wizard of staffs/create and staffs/edit ($staff is null on create): personal data and
    the login account, contacts, professional data, documents, then the weekly schedule (and, when
    editing, the dismissal data and the absences). StaffRequest validates the fields;
    resources/js/pages/staff-form.js runs the schedule, the absences and the photo previews.
--}}
@use('App\Enums\AbsenceType')
@use('App\Enums\Gender')
@use('App\Enums\QualificationCategory')
@use('App\Enums\ScheduleType')
@use('App\Services\Scheduling\SlotGrid')
@php
    $account = $staff?->admin;
    $professional = $staff?->professionalData;
    $contact = $staff?->contact;
    $document = $staff?->document;
    $schedule ??= collect();
    // A new employee starts with reception hours on every day; an existing one keeps the switch where
    // their schedule left it.
    $receptionOff = $staff !== null && $staff->schedule->where('type', ScheduleType::Reception)->isEmpty();
    $selectedServices = array_map('intval', old('services', $staff?->services->pluck('id')->all() ?? []));
    $selectedSurveys = array_map('intval', old('surveys', $staff?->surveys->pluck('id')->all() ?? []));
    $steps = [
        'basic-data' => 'Основные данные',
        'contacts' => 'Контактная информация',
        'employee-details' => 'Проф. данные',
        'documents' => 'Документы',
        'schedule' => 'График работы',
    ];
@endphp

<div class="bs-stepper wizard-vertical vertical" id="staff-wizard">
    <div class="bs-stepper-header" role="tablist" aria-label="Шаги формы">
        @foreach($steps as $pane => $label)
            <div class="step" data-target="#{{ $pane }}">
                <button type="button" class="step-trigger" role="tab" id="{{ $pane }}-trigger" aria-controls="{{ $pane }}">
                    <span class="bs-stepper-circle">{{ $loop->iteration }}</span>
                    <span class="bs-stepper-label">{{ $label }}</span>
                </button>
            </div>
        @endforeach
    </div>

    <div class="bs-stepper-content">
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="staff-form">
            @csrf
            @if($staff)
                @method('PUT')
            @endif

            {{-- 1. Основные данные --}}
            <div id="basic-data" class="content" role="tabpanel" aria-labelledby="basic-data-trigger">
                <div class="content-header mb-3"><h2 class="h3 mb-0">Основные данные</h2></div>
                <div class="row g-3">
                    @include('admin.staffs.partials.input', ['field' => ['name' => 'last_name', 'label' => 'Фамилия', 'icon' => 'bx-rename', 'value' => $staff?->last_name, 'required' => true]])
                    @include('admin.staffs.partials.input', ['field' => ['name' => 'name', 'label' => 'Имя', 'icon' => 'bx-rename', 'value' => $staff?->name, 'required' => true]])
                    @include('admin.staffs.partials.input', ['field' => ['name' => 'middle_name', 'label' => 'Отчество', 'icon' => 'bx-rename', 'value' => $staff?->middle_name, 'required' => true]])
                    @include('admin.staffs.partials.input', ['field' => ['name' => 'date_birthday', 'label' => 'Дата рождения', 'icon' => 'bx-calendar', 'value' => $staff?->date_birthday, 'required' => true, 'class' => 'flatpickr-date', 'placeholder' => 'ГГГГ-ММ-ДД']])

                    <div class="col-md-6 col-xxl-4">
                        <label class="form-label required" for="gender">Пол</label>
                        <select id="gender" name="gender" class="form-select select2 @error('gender') is-invalid @enderror" required>
                            <option value="" disabled @selected(blank(old('gender', $staff?->gender?->value)))>Выберите пол</option>
                            @foreach(Gender::options() as $value => $label)
                                <option value="{{ $value }}" @selected(old('gender', $staff?->gender?->value) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @include('admin.staffs.partials.input', ['field' => ['name' => 'citizenship', 'label' => 'Гражданство', 'icon' => 'bx-world', 'value' => $staff?->citizenship, 'required' => true]])

                    <div class="col-12"><h3 class="subheader mt-2 mb-0">Вход в панель</h3></div>

                    @include('admin.staffs.partials.input', ['field' => ['name' => 'login_email', 'label' => 'Email для входа', 'icon' => 'bx-envelope', 'type' => 'email', 'value' => $account?->email, 'required' => true, 'col' => 'col-md-6', 'placeholder' => 'name@example.com', 'autocomplete' => 'off']])

                    <div class="col-md-6">
                        <label @class(['form-label', 'required' => ! $account]) for="password">Пароль</label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text"><i class="bx bx-lock-alt" aria-hidden="true"></i></span>
                            <input type="password" id="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="{{ $account ? 'Оставьте пустым, чтобы не менять' : 'Не короче 8 символов' }}"
                                   autocomplete="new-password" @required(! $account)
                                   @error('password') aria-describedby="password-error" aria-invalid="true" @enderror>
                            <span class="input-group-text">
                                <button type="button" class="link-secondary border-0 bg-transparent p-0 lh-1" data-password-toggle="#password"
                                        aria-controls="password" aria-pressed="false" aria-label="Показать пароль">
                                    <i class="bx bx-show" aria-hidden="true"></i>
                                </button>
                            </span>
                        </div>
                        @error('password')
                            <div class="invalid-feedback" id="password-error">{{ $message }}</div>
                        @enderror
                    </div>

                    @foreach(['avatar_url' => ['Фото', 'avatar-preview', 'Фото сотрудника'], 'sign_url' => ['Подпись', 'sign-preview', 'Подпись сотрудника']] as $file => [$label, $preview, $alt])
                        <div class="col-md-6">
                            <label class="form-label" for="{{ $file }}">{{ $label }}</label>
                            <input type="file" id="{{ $file }}" name="{{ $file }}" accept="image/jpeg,image/png,image/webp"
                                   class="form-control @error($file) is-invalid @enderror" data-image-preview="{{ $preview }}"
                                   aria-describedby="{{ $file }}-hint">
                            <div class="form-hint" id="{{ $file }}-hint">JPG, PNG или WEBP, не больше 2 МБ.</div>
                            @error($file)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <img id="{{ $preview }}" class="img-thumbnail staff-image-preview mt-2" alt="{{ $alt }}"
                                 @if($staff?->{$file}) src="{{ asset('storage/'.$staff->{$file}) }}" @else hidden @endif>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button class="btn btn-primary btn-next">Далее<i class="bx bx-chevron-right ms-1 me-0" aria-hidden="true"></i></button>
                </div>
            </div>

            {{-- 2. Контактная информация --}}
            <div id="contacts" class="content" role="tabpanel" aria-labelledby="contacts-trigger">
                <div class="content-header mb-3"><h2 class="h3 mb-0">Контактная информация</h2></div>
                <div class="row g-3">
                    @include('admin.staffs.partials.input', ['field' => ['name' => 'phone', 'label' => 'Телефон', 'icon' => 'bx-phone', 'type' => 'tel', 'value' => $contact?->phone, 'required' => true, 'placeholder' => '+998 (XX) XXX XX XX']])
                    @include('admin.staffs.partials.input', ['field' => ['name' => 'address', 'label' => 'Адрес', 'icon' => 'bx-map', 'value' => $contact?->address, 'required' => true, 'col' => 'col-12 col-xxl-8']])

                    <div class="col-12"><h3 class="subheader mt-2 mb-0">Экстренный контакт</h3></div>

                    @include('admin.staffs.partials.input', ['field' => ['name' => 'emergency_contact_name', 'label' => 'ФИО', 'icon' => 'bx-user', 'value' => $contact?->emergency_contact_name, 'placeholder' => 'ФИО экстренного контакта']])
                    @include('admin.staffs.partials.input', ['field' => ['name' => 'emergency_contact_phone', 'label' => 'Телефон', 'icon' => 'bx-phone', 'type' => 'tel', 'value' => $contact?->emergency_contact_phone, 'placeholder' => '+998 (XX) XXX XX XX']])
                    @include('admin.staffs.partials.input', ['field' => ['name' => 'emergency_contact_relation', 'label' => 'Кем приходится', 'icon' => 'bx-group', 'value' => $contact?->emergency_contact_relation, 'placeholder' => 'Например, супруг']])
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button class="btn btn-prev"><i class="bx bx-chevron-left" aria-hidden="true"></i>Назад</button>
                    <button class="btn btn-primary btn-next">Далее<i class="bx bx-chevron-right ms-1 me-0" aria-hidden="true"></i></button>
                </div>
            </div>

            {{-- 3. Проф. данные --}}
            <div id="employee-details" class="content" role="tabpanel" aria-labelledby="employee-details-trigger">
                <div class="content-header mb-3"><h2 class="h3 mb-0">Проф. данные</h2></div>
                <div class="row g-3">
                    <div class="col-md-6 col-xxl-4">
                        <label class="form-label required" for="position_id">Должность</label>
                        <select id="position_id" name="position_id" class="form-select select2 @error('position_id') is-invalid @enderror" required>
                            @foreach($positions as $position)
                                <option value="{{ $position->id }}" @selected($position->id == old('position_id', $professional?->position_id))>{{ $position->name }}</option>
                            @endforeach
                        </select>
                        @error('position_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 col-xxl-4">
                        <label class="form-label" for="addition_position_id">Дополнительная должность</label>
                        {{-- staff-form.js disables the option of the main position: the two must differ. --}}
                        <select id="addition_position_id" name="addition_position_id" class="form-select select2 @error('addition_position_id') is-invalid @enderror">
                            <option value="">Нет</option>
                            @foreach($positions as $position)
                                <option value="{{ $position->id }}" @selected($position->id == old('addition_position_id', $professional?->addition_position_id))>{{ $position->name }}</option>
                            @endforeach
                        </select>
                        @error('addition_position_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 col-xxl-4">
                        <label class="form-label required" for="speciality_id">Специальность</label>
                        <select id="speciality_id" name="speciality_id" class="form-select select2 @error('speciality_id') is-invalid @enderror" required>
                            @foreach($specialties as $specialty)
                                <option value="{{ $specialty->id }}" @selected($specialty->id == old('speciality_id', $professional?->speciality_id))>{{ $specialty->name }}</option>
                            @endforeach
                        </select>
                        @error('speciality_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required" for="category">Категория</label>
                        <select id="category" name="category" class="form-select select2 @error('category') is-invalid @enderror" required>
                            @foreach(QualificationCategory::options() as $value => $label)
                                <option value="{{ $value }}" @selected(old('category', $professional?->category?->value) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required" for="department_id">Отделение</label>
                        <select id="department_id" name="department_id" class="form-select select2 @error('department_id') is-invalid @enderror" required>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected($department->id == old('department_id', $professional?->department_id))>{{ $department->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="services">Услуги</label>
                        <select id="services" name="services[]" class="form-select select2 @error('services') is-invalid @enderror"
                                multiple data-placeholder="Выберите услуги">
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" @selected(in_array($service->id, $selectedServices, true))>{{ $service->name }}</option>
                            @endforeach
                        </select>
                        @error('services')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12"><h3 class="subheader mt-2 mb-0">Образование и сертификат</h3></div>

                    @include('admin.staffs.partials.input', ['field' => ['name' => 'education_name', 'label' => 'Учебное заведение', 'icon' => 'bx-book', 'value' => $professional?->education_name]])
                    @include('admin.staffs.partials.input', ['field' => ['name' => 'education_graduation_year', 'label' => 'Дата окончания обучения', 'icon' => 'bx-calendar', 'value' => $professional?->education_graduation_year, 'class' => 'flatpickr-date', 'placeholder' => 'ГГГГ-ММ-ДД']])
                    @include('admin.staffs.partials.input', ['field' => ['name' => 'certificate_name', 'label' => 'Название сертификата', 'icon' => 'bx-certification', 'value' => $professional?->certificate_name]])
                    @include('admin.staffs.partials.input', ['field' => ['name' => 'certificate_number', 'label' => 'Номер лицензии', 'icon' => 'bx-id-card', 'value' => $professional?->certificate_number]])
                    @include('admin.staffs.partials.input', ['field' => ['name' => 'certificate_issue_date', 'label' => 'Дата выдачи', 'icon' => 'bx-calendar', 'value' => $professional?->certificate_issue_date, 'class' => 'flatpickr-date', 'placeholder' => 'ГГГГ-ММ-ДД']])
                    @include('admin.staffs.partials.input', ['field' => ['name' => 'certificate_validity_period', 'label' => 'Срок действия', 'icon' => 'bx-time-five', 'value' => $professional?->certificate_validity_period, 'class' => 'flatpickr-date', 'placeholder' => 'ГГГГ-ММ-ДД']])
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button class="btn btn-prev"><i class="bx bx-chevron-left" aria-hidden="true"></i>Назад</button>
                    <button class="btn btn-primary btn-next">Далее<i class="bx bx-chevron-right ms-1 me-0" aria-hidden="true"></i></button>
                </div>
            </div>

            {{-- 4. Документы --}}
            <div id="documents" class="content" role="tabpanel" aria-labelledby="documents-trigger">
                <div class="content-header mb-3"><h2 class="h3 mb-0">Документы</h2></div>
                <div class="row g-3">
                    @include('admin.staffs.partials.input', ['field' => ['name' => 'employment_contract_number', 'label' => 'Номер трудового договора', 'icon' => 'bx-file', 'value' => $document?->employment_contract_number, 'required' => true]])
                    @include('admin.staffs.partials.input', ['field' => ['name' => 'employment_contract_date_of_signing', 'label' => 'Дата подписания договора', 'icon' => 'bx-calendar', 'value' => $document?->employment_contract_date_of_signing, 'required' => true, 'class' => 'flatpickr-date', 'placeholder' => 'ГГГГ-ММ-ДД']])
                    @include('admin.staffs.partials.input', ['field' => ['name' => 'employment_contract_validity_period', 'label' => 'Срок действия договора', 'icon' => 'bx-calendar-check', 'value' => $document?->employment_contract_validity_period, 'required' => true]])

                    @if($staff)
                        <div class="col-12"><h3 class="subheader mt-2 mb-0">Увольнение</h3></div>

                        @include('admin.staffs.partials.input', ['field' => ['name' => 'dismissal_orders_number', 'label' => 'Номер приказа об увольнении', 'icon' => 'bx-file', 'value' => $document?->dismissal_orders_number, 'col' => 'col-md-6']])
                        @include('admin.staffs.partials.input', ['field' => ['name' => 'dismissal_orders_date', 'label' => 'Дата приказа об увольнении', 'icon' => 'bx-calendar', 'value' => $document?->dismissal_orders_date, 'class' => 'flatpickr-date', 'placeholder' => 'ГГГГ-ММ-ДД', 'col' => 'col-md-6']])
                        @include('admin.staffs.partials.input', ['field' => ['name' => 'dismissal_reception_number', 'label' => 'Номер акта приёма увольнения', 'icon' => 'bx-file', 'value' => $document?->dismissal_reception_number, 'col' => 'col-md-6']])
                        @include('admin.staffs.partials.input', ['field' => ['name' => 'dismissal_reception_date', 'label' => 'Дата увольнения', 'icon' => 'bx-calendar', 'value' => $document?->dismissal_reception_date, 'class' => 'flatpickr-date', 'placeholder' => 'ГГГГ-ММ-ДД', 'col' => 'col-md-6']])
                    @endif

                    <div class="col-12"><h3 class="subheader mt-2 mb-0">Удостоверения</h3></div>

                    @include('admin.staffs.partials.input', ['field' => ['name' => 'passport_series', 'label' => 'Серия паспорта', 'icon' => 'bx-id-card', 'value' => $document?->passport_series, 'required' => true]])
                    @include('admin.staffs.partials.input', ['field' => ['name' => 'INN', 'label' => 'ИНН', 'icon' => 'bx-id-card', 'value' => $document?->INN, 'required' => true, 'inputmode' => 'numeric']])
                    @include('admin.staffs.partials.input', ['field' => ['name' => 'SNILS', 'label' => 'СНИЛС', 'icon' => 'bx-id-card', 'value' => $document?->SNILS, 'required' => true]])

                    <div class="col-12">
                        <label class="form-label" for="surveys">Мед. документы</label>
                        <select id="surveys" name="surveys[]" class="form-select select2 @error('surveys') is-invalid @enderror"
                                multiple data-placeholder="Выберите документы, которые заполняет сотрудник">
                            @foreach($surveys as $survey)
                                <option value="{{ $survey->id }}" @selected(in_array($survey->id, $selectedSurveys, true))>{{ $survey->title }}</option>
                            @endforeach
                        </select>
                        @error('surveys')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button class="btn btn-prev"><i class="bx bx-chevron-left" aria-hidden="true"></i>Назад</button>
                    <button class="btn btn-primary btn-next">Далее<i class="bx bx-chevron-right ms-1 me-0" aria-hidden="true"></i></button>
                </div>
            </div>

            {{-- 5. График работы --}}
            <div id="schedule" class="content" role="tabpanel" aria-labelledby="schedule-trigger">
                <div class="content-header d-flex flex-wrap gap-3 align-items-center justify-content-between mb-3">
                    <h2 class="h3 mb-0">График работы</h2>
                    <label class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="disable-reception" data-reception-off @checked($receptionOff)>
                        <span class="form-check-label">Без часов приёма</span>
                    </label>
                </div>

                <div class="row g-3">
                    <div class="col-md-8 col-xxl-6">
                        <label class="form-label" for="admission_period">Период приёма, минут</label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text"><i class="bx bx-time-five" aria-hidden="true"></i></span>
                            <input type="number" id="admission_period" name="admission_period"
                                   value="{{ old('admission_period', $professional?->admission_period) }}"
                                   min="{{ SlotGrid::MIN_MINUTES }}" max="{{ SlotGrid::MAX_MINUTES }}" step="1"
                                   class="form-control @error('admission_period') is-invalid @enderror" placeholder="Например, 30"
                                   aria-describedby="admission_period-hint @error('admission_period') admission_period-error @enderror">
                        </div>
                        <div class="form-hint" id="admission_period-hint">Длительность одной записи; нужна, если у сотрудника есть часы приёма.</div>
                        @error('admission_period')
                            <div class="invalid-feedback" id="admission_period-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-3" id="weekly-schedule">
                    @foreach($days as $i => $day)
                        @php
                            $workHours = $schedule->get($day->id)?->get(ScheduleType::Work->value) ?? collect();
                            $receptionHours = $schedule->get($day->id)?->get(ScheduleType::Reception->value) ?? collect();
                            $isWorkDay = $staff === null || $workHours->isNotEmpty();
                            // Default hours: 08:30–17:30 of work, and for a new employee 08:00–17:30 of reception.
                            $work = $workHours->isEmpty()
                                ? [['08:30', '17:30']]
                                : $workHours->values()->map(fn ($hours): array => [substr($hours->start_time, 0, 5), substr($hours->end_time, 0, 5)])->all();
                            $reception = $staff === null
                                ? [['08:00', '17:30']]
                                : $receptionHours->values()->map(fn ($hours): array => [substr($hours->start_time, 0, 5), substr($hours->end_time, 0, 5)])->all();
                        @endphp
                        <section @class(['schedule-day', 'is-off' => ! $isWorkDay]) data-schedule-day aria-labelledby="day-{{ $day->id }}-title">
                            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
                                <h3 class="h4 mb-0" id="day-{{ $day->id }}-title">{{ $day->name }}</h3>
                                <label class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" data-day-toggle @checked($isWorkDay)>
                                    <span class="form-check-label">Рабочий день</span>
                                </label>
                            </div>

                            <input type="hidden" name="days[{{ $i }}][id]" value="{{ $day->id }}" data-day-id @disabled(! $isWorkDay)>

                            <div class="row g-3">
                                @foreach(['work' => ['График работы', 'Добавить рабочий интервал', $work], 'reception' => ['График приёма', 'Добавить интервал приёма', $reception]] as $type => [$title, $add, $ranges])
                                    @php($disabled = ! $isWorkDay || ($type === 'reception' && $receptionOff))
                                    <div class="col-xxl-6 schedule-block" @if($type === 'reception') data-reception-block @endif>
                                        <h4 class="subheader mb-2">{{ $title }}</h4>
                                        <div data-ranges="{{ $type }}" data-day="{{ $i }}" data-next-index="{{ count($ranges) }}">
                                            @foreach($ranges as $index => [$start, $end])
                                                @include('admin.staffs.partials.time-range', ['range' => [
                                                    'day' => $i, 'type' => $type, 'index' => $index, 'start' => $start, 'end' => $end, 'disabled' => $disabled,
                                                ]])
                                            @endforeach
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-add-range="{{ $type }}" @disabled($disabled)>
                                            <i class="bx bx-plus" aria-hidden="true"></i>{{ $add }}
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>

                @if($staff)
                    <section class="mt-4" aria-labelledby="absence-title">
                        <h3 class="h4" id="absence-title">Нерабочие дни и периоды</h3>
                        <div data-absences data-next-index="{{ $staff->absence->count() }}" data-period-type="{{ AbsenceType::Period->value }}">
                            @foreach($staff->absence->values() as $index => $absence)
                                @include('admin.staffs.partials.absence', ['absence' => [
                                    'index' => $index,
                                    'type' => $absence->type,
                                    'reason' => $absence->reason,
                                    'date' => $absence->type === AbsenceType::Period
                                        ? $absence->start_date->toDateString().' to '.($absence->end_date ?? $absence->start_date)->toDateString()
                                        : $absence->start_date->toDateString(),
                                ]])
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-add-absence>
                            <i class="bx bx-plus" aria-hidden="true"></i>Добавить нерабочий день или период
                        </button>
                    </section>
                @endif

                <div class="d-flex justify-content-between mt-4">
                    <button class="btn btn-prev"><i class="bx bx-chevron-left" aria-hidden="true"></i>Назад</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save" aria-hidden="true"></i>Сохранить</button>
                </div>
            </div>
        </form>
    </div>
</div>

<template id="time-range-template">
    @include('admin.staffs.partials.time-range', ['range' => ['template' => true]])
</template>

@if($staff)
    <template id="absence-template">
        @include('admin.staffs.partials.absence', ['absence' => ['template' => true]])
    </template>
@endif
