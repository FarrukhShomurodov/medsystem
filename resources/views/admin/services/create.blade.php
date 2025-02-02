@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Создать услугу</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('services.index') }}">Услуги</a> /</span>Создать
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
            <form action="{{ route('services.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

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
                                   value="{{ old('name') }}"
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
                                   value="{{ old('code') }}" aria-describedby="basic-icon-default-middle-name2"/>
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
                                   value="{{ old('price') }}" aria-describedby="basic-icon-default-middle-name2"/>
                        </div>
                        @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="basic-icon-default-discount">Скидка</label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-middle-name2"
                                  class="input-group-text @error('discount') text-danger icon-border @enderror">
                                <i class="bx bx-tag"></i>
                            </span>
                            <input type="number" id="basic-icon-default-discount"
                                   class="form-control @error('discount') is-invalid @enderror"
                                   placeholder="Скидка" aria-label="discount" name="discount"
                                   value="{{ old('discount') }}" aria-describedby="basic-icon-default-middle-name2"/>
                        </div>
                        @error('discount')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="basic-icon-default-currency">Валюта </label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-middle-name2"
                                  class="input-group-text @error('currency') text-danger icon-border @enderror">
                                <i class="bx bx-money"></i>
                            </span>
                            <input type="text" id="basic-icon-default-currency"
                                   class="form-control @error('currency') is-invalid @enderror"
                                   placeholder="Валюта" aria-label="currency" name="currency"
                                   value="{{ old('currency') }}" aria-describedby="basic-icon-default-middle-name2"/>
                        </div>
                        @error('currency')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="basic-icon-default-duration">Длительность оказания услуги  </label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-middle-name2"
                                  class="input-group-text @error('duration') text-danger icon-border @enderror">
                                <i class="bx bx-time"></i>
                            </span>
                            <input type="text" id="basic-icon-default-duration"
                                   class="form-control @error('duration') is-invalid @enderror"
                                   placeholder="Длительность оказания услуги" aria-label="duration" name="duration"
                                   value="{{ old('duration') }}" aria-describedby="basic-icon-default-middle-name2"/>
                        </div>
                        @error('duration')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
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
                                  aria-describedby="basic-icon-default-last-name2">{{ old('description') }}</textarea>
                    </div>
                    @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="specialty">Категории <span
                            class="text-danger">*</span></label>

                    <select name="service_category_id"
                            class="select2 form-control @error('service_category_id') is-invalid @enderror"
                            id="specialty"
                            required>
                        <option value="" disabled selected>Выберите категорию</option>

                        @foreach($categories as $category)
                            <option
                                value="{{ $category->id }}" @selected($category->id == old('service_category_id'))> {{ $category->name }} </option>
                        @endforeach
                    </select>

                    @error('service_category_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>
    </div>
@endsection
