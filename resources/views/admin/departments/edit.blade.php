@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Отделение</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('departments.index') }}">Отделение </a> /</span>Редактировать
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
                    <span class="bs-stepper-label">Рабочие данные</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#licenses">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">4</span>
                    <span class="bs-stepper-label">Лицензии</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#ralations">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">5</span>
                    <span class="bs-stepper-label">Свять с отделениями</span>
                </button>
            </div>
            <div class="line"></div>
            <div class="step" data-target="#resources">
                <button type="button" class="step-trigger">
                    <span class="bs-stepper-circle">6</span>
                    <span class="bs-stepper-label">Ресурсы</span>
                </button>
            </div>
        </div>

        <div class="bs-stepper-content">
            <form method="post" action="{{ route('departments.update', $department->id) }}"
                  enctype="multipart/form-data">
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
                                  class="input-group-text @error('name') text-danger icon-border @enderror"><i
                                    class="bx bx-rename"></i></span>
                                <input type="text" id="basic-icon-default-company"
                                       class="form-control @error('name') is-invalid @enderror"
                                       placeholder="Название" aria-label="Название"
                                       name="name"
                                       value="{{ old('name', $department->name) }}"
                                       aria-describedby="basic-icon-default-company2" required/>
                            </div>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="type">Типы <span
                                    class="text-danger">*</span></label>
                            <select id="type" name="type_id"
                                    class="form-control select2 @error('type_id') is-invalid @enderror" required>
                                @foreach($types as $type)
                                    <option
                                        value="{{ $type->id }}" @selected(old('type_id', $department->type_id) == $type->id)>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type_id')
                            <div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="floor">Этаж <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="floor"
                                  class="input-group-text @error('floor') text-danger icon-border @enderror"><i
                                    class="bx bx-rename"></i></span>
                                <input type="number" id="floor"
                                       class="form-control @error('floor') is-invalid @enderror"
                                       placeholder="Этаж" aria-label="Этаж"
                                       name="floor"
                                       value="{{ old('floor', $department->floor) }}"
                                       aria-describedby="floor" required/>
                            </div>
                            @error('floor')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="basic-icon-default-description">Описание </label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-default-history2"
                                      class="input-group-text @error('description') text-danger icon-border @enderror">
                                    <i class="bx bx-notepad"></i>
                                </span>
                                <textarea id="basic-icon-default-description"
                                          class="form-control @error('description') is-invalid @enderror"
                                          placeholder="Описание"
                                          aria-label="Описание"
                                          name="description"
                                          aria-describedby="basic-icon-default-history2">{{ old('description', $department->description) }}</textarea>
                            </div>
                            @error('description')
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

                <!-- Контактная информация -->
                <div id="contacts" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Контактная информация</h6>
                    </div>
                    <div class="row g-3">

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-phone">Телефон </label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-phone2"
                                  class="input-group-text @error('phone') text-danger icon-border @enderror"><i
                                    class="bx bx-phone"></i></span>
                                <input type="text" id="basic-icon-default-phone"
                                       class="form-control phone @error('phone') is-invalid @enderror"
                                       name="phone"
                                       value="{{ old('phone', $department->contact->phone) }}"
                                       placeholder="+ 998 (XX) XXX XX XX" aria-label="+ 998 (XX) XXX XX XX"
                                       aria-describedby="basic-icon-default-phone2"/>
                            </div>

                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-address">
                                Адрес
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
                                       value="{{ old('address', $department->contact->address) }}"
                                       aria-describedby="basic-icon-default-company2"/>
                            </div>

                            @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-email">Email</label>
                            <div class="input-group input-group-merge">
                            <span class="input-group-text @error('email') text-danger icon-border @enderror"><i
                                    class="bx bx-envelope"></i></span>
                                <input type="email" id="basic-icon-default-email"
                                       class="form-control @error('email') is-invalid @enderror" placeholder="Email"
                                       name="email"
                                       value="{{ old('email', $department->contact->email) }}"
                                       aria-label="Email" aria-describedby="basic-icon-default-email2"/>
                            </div>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="basic-icon-default-fax">Fax</label>
                            <div class="input-group input-group-merge">
                            <span class="input-group-text @error('fax') text-danger icon-border @enderror"><i
                                    class="bx bx-printer"></i></span>
                                <input type="text" id="basic-icon-default-fax"
                                       class="form-control @error('fax') is-invalid @enderror" placeholder="Fax"
                                       name="fax"
                                       value="{{ old('fax', $department->contact->fax) }}"
                                       aria-label="Fax" aria-describedby="basic-icon-default-fax2"/>
                            </div>
                            @error('fax')
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

                <!-- Рабочие данные -->
                <div id="employee_details" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Рабочие данные</h6>
                    </div>
                    <div class="row g-3">
                        <!-- total_beds -->
                        {{--                        <div class="col-sm-6">--}}
                        {{--                            <label class="form-label" for="basic-icon-default-total_beds">Количество коек <span--}}
                        {{--                                    class="text-danger">*</span></label>--}}
                        {{--                            <div class="input-group input-group-merge">--}}
                        {{--                            <span id="basic-icon-default-total_beds"--}}
                        {{--                                  class="input-group-text @error('total_beds') text-danger icon-border @enderror"><i--}}
                        {{--                                    class="bx bx-bed"></i></span>--}}
                        {{--                                <input type="number" id="basic-icon-default-total_beds"--}}
                        {{--                                       class="form-control @error('total_beds') is-invalid @enderror"--}}
                        {{--                                       name="total_beds"--}}
                        {{--                                       value="{{ old('total_beds', $department->bed->total_beds) }}"--}}
                        {{--                                       placeholder="Количество коек" aria-label="Количество коек"--}}
                        {{--                                       aria-describedby="basic-icon-default-total_beds"/>--}}
                        {{--                            </div>--}}

                        {{--                            @error('total_beds')--}}
                        {{--                            <div class="invalid-feedback">{{ $message }}</div>--}}
                        {{--                            @enderror--}}
                        {{--                        </div>--}}

                        {{--                        <!-- occupied_beds -->--}}
                        {{--                        <div class="col-sm-6">--}}
                        {{--                            <label class="form-label" for="basic-icon-default-occupied_beds">Занятые койки <span--}}
                        {{--                                    class="text-danger">*</span></label>--}}
                        {{--                            <div class="input-group input-group-merge">--}}
                        {{--                            <span id="basic-icon-default-occupied_beds"--}}
                        {{--                                  class="input-group-text @error('occupied_beds') text-danger icon-border @enderror"><i--}}
                        {{--                                    class="bx bx-bed"></i></span>--}}
                        {{--                                <input type="number" id="basic-icon-default-occupied_beds"--}}
                        {{--                                       class="form-control @error('occupied_beds') is-invalid @enderror"--}}
                        {{--                                       name="occupied_beds"--}}
                        {{--                                       value="{{ old('occupied_beds', $department->bed->occupied_beds) }}"--}}
                        {{--                                       placeholder="Занятые койки" aria-label="Занятые койки"--}}
                        {{--                                       aria-describedby="basic-icon-default-occupied_beds"/>--}}
                        {{--                            </div>--}}

                        {{--                            @error('occupied_beds')--}}
                        {{--                            <div class="invalid-feedback">{{ $message }}</div>--}}
                        {{--                            @enderror--}}
                        {{--                        </div>--}}

                        <div class="col-12">
                            <label class="form-label">Персонал (Осв. дан)</label>
                            <ul class="list-group">
                                @forelse($department->staffs as $staffData)
                                    <li class="list-group-item">
                                        <strong>Должность:</strong>
                                        {{ optional($staffData->position)->name ?? '—' }}
                                        @if ($staffData->additionPosition)
                                            , {{ optional($staffData->additionPosition)->name }}
                                        @endif
                                        <br>
                                        <strong>Персонал:</strong> {{ optional($staffData->staff)->last_name }} {{ optional($staffData->staff)->name }} {{ optional($staffData->staff)->middle_name }}
                                    </li>
                                @empty
                                    <li class="list-group-item text-muted">Персонал не назначен</li>
                                @endforelse
                            </ul>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="warehouse_id">Склады <span
                                    class="text-danger">*</span></label>

                            <select id="warehouse_id" name="warehouse_id"
                                    class="form-control select2 @error('warehouse_id') is-invalid @enderror">
                                @foreach($warehouses as $warehouse)
                                    <option
                                        value="{{ $warehouse->id }}" @selected(old('warehouse_id', $department->warehouse_id) == $warehouse->id)>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('warehouse_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="rooms">Палаты <span
                                    class="text-danger">*</span></label>

                            <select id="rooms" name="rooms[]"
                                    class="form-control select2 @error('rooms') is-invalid @enderror" required
                                    multiple>
                                @foreach($wardRooms as $room)
                                    <option
                                        value="{{ $room->id }}" @selected(in_array($room->id, old('rooms', $department->rooms->pluck('id')->toArray())) )>
                                        {{ $room->room_number }} (Койки: {{ $room->beds->count() }})
                                    </option>
                                @endforeach
                            </select>

                            @error('rooms')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="services">Услуги <span
                                    class="text-danger">*</span></label>

                            <select id="services" name="services[]"
                                    class="form-control select2 @error('services') is-invalid @enderror" required
                                    multiple>
                                @foreach($services as $service)
                                    <option
                                        value="{{ $service->id }}" @selected(in_array($service->id, old('services', $department->services->pluck('id')->toArray())) )>
                                        {{ $service->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('services')
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

                <!-- Лицензии -->
                <div id="licenses" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Лицензии</h6>
                    </div>
                    <div class="row g-3">
                        <!-- license_number -->
                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-default-license_number">Номер лицензии <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-license_number"
                                  class="input-group-text @error('license_number') text-danger icon-border @enderror"><i
                                    class="bx bx-id-card"></i></span>
                                <input type="text" id="basic-icon-default-license_number"
                                       class="form-control @error('license_number') is-invalid @enderror"
                                       name="license_number"
                                       value="{{ old('license_number', $department->license->license_number) }}"
                                       placeholder="Номер лицензии" aria-label="Номер лицензии"
                                       aria-describedby="basic-icon-default-license_number"/>
                            </div>

                            @error('license_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label" for="basic-icon-expiration_date">Срок действия лицензии <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span id="basic-icon-birth-date2"
                                      class="input-group-text @error('expiration_date') text-danger icon-border @enderror">
                                    <i class="bx bx-calendar"></i>
                                </span>
                                <input type="text" name="expiration_date"
                                       value="{{ old('expiration_date', $department->license->expiration_date) }}"
                                       id="basic-icon-expiration_date"
                                       class="form-control flatpickr-date @error('expiration_date') is-invalid @enderror"
                                       placeholder="YYYY-MM-DD" aria-describedby="basic-icon-birth-date2" required/>
                            </div>
                            @error('expiration_date')
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

                <!-- Свять с отделениями -->
                <div id="ralations" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Свять с отделениями</h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="relations">Отделения<span
                                    class="text-danger">*</span></label>

                            <select id="relations" name="relations[]"
                                    class="form-control select2 @error('relations') is-invalid @enderror"
                                    multiple>
                                @foreach($departments as $department)
                                    <option
                                        value="{{ $department->id }}" @selected(in_array($department->id, old('relations', $department->relations->pluck('id')->toArray())) )>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('relations')
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

                <!-- Ресурсы -->
                <div id="resources" class="content">
                    <div class="content-header mb-3">
                        <h6 class="mb-0">Ресурсы</h6>
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="equipments">Оборудование<span
                                    class="text-danger">*</span></label>

                            <select id="equipments" name="equipments[]"
                                    class="form-control select2 @error('equipments') is-invalid @enderror" required
                                    multiple>
                                @foreach($equipments as $equipment)
                                    <option
                                        value="{{ $equipment->id }}" @selected(in_array($equipment->id, old('equipments', $department->equipments->pluck('id')->toArray())) )>
                                        {{ $equipment->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('equipments')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
            // Initialize Flatpickr
            flatpickr('.flatpickr-date', {dateFormat: 'Y-m-d'});
            flatpickr('.flatpickr-time', {enableTime: true, noCalendar: true, time_24hr: true});
        });
    </script>
@endsection
