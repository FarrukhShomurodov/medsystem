@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Создать поставшика</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('suppliers.index') }}">Поставшики </a> /</span>Создать
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
            <form action="{{ route('suppliers.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-sm-4 mb-3">
                        <label class="form-label" for="basic-icon-default-company">Название <span
                                class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                        <span class="input-group-text @error('name') text-danger icon-border @enderror">
                            <i class="bx bx-rename"></i>
                        </span>
                            <input type="text" id="basic-icon-default-company"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="Название" name="name"
                                   value="{{ old('name') }}" required/>
                        </div>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-sm-4 mb-3">
                        <label class="form-label" for="basic-icon-default-contract_terms">Условия контракта </label>
                        <div class="input-group input-group-merge">
                            <input type="file" id="basic-icon-default-contract_terms"
                                   class="form-control @error('contract_terms') is-invalid @enderror"
                                   name="contract_terms" required/>
                        </div>
                        @error('contract_terms')
                        <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-sm-4 mb-3">
                        <label class="form-label" for="basic-icon-default-phone">Телефон <span
                                class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                        <span class="input-group-text @error('phone') text-danger icon-border @enderror">
                            <i class="bx bx-phone"></i>
                        </span>
                            <input type="text" id="basic-icon-default-phone"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   placeholder="Телефон" name="phone"
                                   value="{{ old('phone') }}" required/>
                        </div>
                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-sm-4 mb-3">
                        <label class="form-label" for="basic-icon-default-email">Email <span
                                class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                        <span class="input-group-text @error('email') text-danger icon-border @enderror">
                            <i class="bx bx-envelope"></i>
                        </span>
                            <input type="text" id="basic-icon-default-email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   placeholder="Email" name="email"
                                   value="{{ old('email') }}" required/>
                        </div>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-sm-4 mb-3">
                        <label class="form-label" for="basic-icon-default-address">Адрес <span
                                class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                        <span class="input-group-text @error('address') text-danger icon-border @enderror">
                            <i class="bx bx-map"></i>
                        </span>
                            <input type="text" id="basic-icon-default-address"
                                   class="form-control @error('address') is-invalid @enderror"
                                   placeholder="Адрес" name="address"
                                   value="{{ old('address') }}" required/>
                        </div>
                        @error('address')
                        <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-sm-4 mb-3">
                        <label class="form-label" for="basic-icon-default-delivery_schedule">Расписание <span
                                class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                        <span class="input-group-text @error('delivery_schedule') text-danger icon-border @enderror">
                            <i class="bx bx-calendar"></i>
                        </span>
                            <input type="text" id="basic-icon-default-delivery_schedule"
                                   class="form-control @error('delivery_schedule') is-invalid @enderror"
                                   placeholder="Расписание" name="delivery_schedule"
                                   value="{{ old('delivery_schedule') }}" required/>
                        </div>
                        @error('delivery_schedule')
                        <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label" for="manufacturers">Производители<span
                                class="text-danger">*</span></label>
                        <select id="manufacturers" name="manufacturers[]"
                                class="form-control select2 @error('manufacturers') is-invalid @enderror" multiple
                                required>
                            @foreach($manufacturers as $manufacturer)
                                <option
                                    value="{{ $manufacturer->id }}" @selected(in_array($manufacturer->id, old('manufacturers',[])))>
                                    {{ $manufacturer->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('manufacturers')
                        <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>
    </div>
@endsection
