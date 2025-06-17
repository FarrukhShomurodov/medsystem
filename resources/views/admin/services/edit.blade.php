@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Редактировать услугу</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('services.index') }}">Услуги</a> /</span>Редактировать
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
            <h5 class="mb-0">Редактировать</h5>

            <label class="switch" style="margin-right: 40px">
                <input type="checkbox" class="switch-input switch-active" name="is_active" @if($service->is_active) checked @endif>
                <span class="switch-toggle-slider">
                    <span class="switch-on"></span>
                    <span class="switch-off"></span>
                </span>
            </label>
        </div>
        <div class="card-body">
            <form action="{{ route('services.update', $service->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('put')

                <input type="number" class="is_active" name="is_active" hidden="">

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="basic-icon-default-name">Наименование <span
                                class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-name2"
                                  class="input-group-text @error('name') text-danger icon-border @enderror">
                                <i class="bx bx-rename"></i>
                            </span>
                            <input type="text" id="basic-icon-default-name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="Наименование" aria-label="Наименование" name="name"
                                   value="{{ old('name', $service->name) }}"
                                   aria-describedby="basic-icon-default-name2" required/>
                        </div>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="basic-icon-default-code">Код </label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-middle-name2"
                                  class="input-group-text @error('code') text-danger icon-border @enderror">
                                <i class="bx bx-barcode"></i>
                            </span>
                            <input type="text" id="basic-icon-default-code"
                                   class="form-control @error('code') is-invalid @enderror"
                                   placeholder="Код" aria-label="code" name="code"
                                   value="{{ old('code', $service->code) }}"
                                   aria-describedby="basic-icon-default-middle-name2"/>
                        </div>
                        @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="basic-icon-default-price">Стоимость <span
                                class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-middle-name2"
                                  class="input-group-text @error('price') text-danger icon-border @enderror">
                                <i class="bx bx-dollar"></i>
                            </span>
                            <input type="number" id="basic-icon-default-price"
                                   class="form-control @error('price') is-invalid @enderror"
                                   placeholder="Стоимость" aria-label="price" name="price"
                                   required
                                   value="{{ old('price', $service->price) }}"
                                   aria-describedby="basic-icon-default-middle-name2"/>
                        </div>
                        @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="basic-icon-default-discount">Скидка</label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-middle-name2"
                                  class="input-group-text @error('discount') text-danger icon-border @enderror">
                                <i class="bx bx-tag"></i>
                            </span>
                            <input type="number" id="basic-icon-default-discount"
                                   class="form-control @error('discount') is-invalid @enderror"
                                   placeholder="Скидка" aria-label="discount" name="discount"
                                   value="{{ old('discount', $service->discount) }}"
                                   aria-describedby="basic-icon-default-middle-name2"/>
                        </div>
                        @error('discount')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="basic-icon-default-currency">Валюта </label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-middle-name2"
                                  class="input-group-text @error('currency') text-danger icon-border @enderror">
                                <i class="bx bx-money"></i>
                            </span>
                            <input type="text" id="basic-icon-default-currency"
                                   class="form-control @error('currency') is-invalid @enderror"
                                   placeholder="Валюта" aria-label="currency" name="currency"
                                   value="{{ old('currency', $service->currency) }}"
                                   aria-describedby="basic-icon-default-middle-name2"/>
                        </div>
                        @error('currency')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label" for="basic-icon-default-description"> Описание </label>
                        <div class="input-group input-group-merge">
                        <span id="basic-icon-default-last-name2"
                              class="input-group-text @error('description') text-danger icon-border @enderror">
                            <i class="bx bx-edit"></i>
                        </span>
                            <textarea id="basic-icon-default-description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Описание" aria-label="Описание" name="description"
                                      aria-describedby="basic-icon-default-last-name2">{{ old('description', $service->description) }}</textarea>
                        </div>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="specialty">Группы <span
                                class="text-danger">*</span></label>

                        <select name="service_group_id"
                                class="select2 form-control @error('service_group_id') is-invalid @enderror"
                                id="specialty"
                                required>
                            <option value="" disabled selected>Выберите группу</option>

                            @foreach($groups as $group)
                                <option
                                    value="{{ $group->id }}" @selected($group->id == old('service_group_id', $service->service_group_id))> {{ $group->name }} </option>
                            @endforeach
                        </select>

                        @error('service_group_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="service_type">Типы <span
                                class="text-danger">*</span></label>

                        <select name="service_type_id"
                                class="select2 form-control @error('service_type_id') is-invalid @enderror"
                                id="service_type"
                                required>
                            <option value="" disabled selected>Выберите тип</option>

                            @foreach($types as $type)
                                <option
                                    value="{{ $type->id }}" @selected($type->id == old('service_type_id',  $service->service_type_id))> {{ $type->name }} </option>
                            @endforeach
                        </select>

                        @error('service_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mb-3 d-flex align-items-center flex-row ">
                        <label for="is_appointment">По записи</label>

                        <input type="hidden" name="is_appointment" class="is_appointment">

                        <label class="switch">
                            <input type="checkbox" class="switch-appointment switch-input" @checked($service->is_appointment)>
                            <span class="switch-toggle-slider ms-1">
                                <span class="switch-on"></span>
                                <span class="switch-off"></span>
                            </span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-warning ">Редактировать</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            @if($service->is_active)
                $('.is_active').val(1);
            @else
                $('.is_active').val(0);
            @endif

            $('.switch-active').on('change', function () {
                let isActive = $(this).is(':checked') ? 1 : 0;
                $('.is_active').val(isActive);
            });

            @if($service->is_appointment)
                $('.is_appointment').val(1);
            @else
                $('.is_appointment').val(0);
            @endif

            $('.switch-appointment').on('change', function () {
                let isActive = $(this).is(':checked') ? 1 : 0;
                $('.is_appointment').val(isActive);
            });
        });
    </script>
@endsection
