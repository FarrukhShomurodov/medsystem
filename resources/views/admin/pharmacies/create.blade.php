@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Аптеки</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('pharmacies.index') }}">Аптеки </a> /</span>Создать
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
        </div>

        <div class="bs-stepper-content">
            <form method="post" action="{{ route('pharmacies.store') }}" enctype="multipart/form-data">
                @csrf

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
                                       value="{{ old('name') }}"
                                       aria-describedby="basic-icon-default-company2"/>
                            </div>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="type">Типы <span
                                    class="text-danger">*</span></label>
                            <select id="type" name="type_id"
                                    class="form-control select2 @error('type_id') is-invalid @enderror">
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}" @selected(old('type_id') == $type->id)>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type_id')
                            <div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label" for="basic-icon-default-legal_address">Юридический адрес <span
                                    class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('legal_address') text-danger icon-border @enderror"><i
                                    class="bx bx-legal_address"></i></span>
                                <input type="text" id="basic-icon-default-legal_address"
                                       class="form-control @error('legal_address') is-invalid @enderror"
                                       placeholder="Юридический адрес" aria-label="Юридический адрес"
                                       name="legal_address"
                                       value="{{ old('legal_address') }}"
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
                                  class="input-group-text @error('actual_address') text-danger icon-border @enderror"><i
                                    class="bx bx-actual_address"></i></span>
                                <input type="text" id="basic-icon-default-actual_address"
                                       class="form-control @error('actual_address') is-invalid @enderror"
                                       placeholder="Фактический адрес" aria-label="Фактический адрес"
                                       name="actual_address"
                                       value="{{ old('actual_address') }}"
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
                                  class="input-group-text @error('contact_phone') text-danger icon-border @enderror"><i
                                    class="bx bx-contact_phone"></i></span>
                                <input type="text" id="basic-icon-default-contact_phone"
                                       class="form-control @error('contact_phone') is-invalid @enderror"
                                       placeholder="Контактный телефон" aria-label="Контактный телефон"
                                       name="contact_phone"
                                       value="{{ old('contact_phone') }}"
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
                                  class="input-group-text @error('working_hours') text-danger icon-border @enderror"><i
                                    class="bx bx-working_hours"></i></span>
                                <input type="text" id="basic-icon-default-working_hours"
                                       class="form-control @error('working_hours') is-invalid @enderror"
                                       placeholder="Режим работы" aria-label="Режим работы"
                                       name="working_hours"
                                       value="{{ old('working_hours') }}"
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
