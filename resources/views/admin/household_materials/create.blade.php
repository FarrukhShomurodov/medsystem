@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Создать хозяйственный материал</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('household.materials.index') }}">Хозяйственные материалы </a> /</span>Создать
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
            <form action="{{ route('household.materials.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Название (уже есть) --}}
                <div class="mb-3">
                    <label class="form-label" for="basic-icon-default-company">Название <span class="text-danger">*</span></label>
                    <div class="input-group input-group-merge">
            <span id="basic-icon-default-company2"
                  class="input-group-text @error('name') text-danger icon-border @enderror"><i class="bx bx-rename"></i></span>
                        <input type="text" id="basic-icon-default-company"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Название" aria-label="Название"
                               name="name"
                               value="{{ old('name') }}"
                               aria-describedby="basic-icon-default-company2" required/>
                    </div>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Номер партии --}}
                <div class="mb-3">
                    <label class="form-label" for="batch_number">Номер партии <span class="text-danger">*</span></label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text @error('batch_number') text-danger icon-border @enderror"><i class="bx bx-barcode"></i></span>
                        <input type="text" id="batch_number"
                               class="form-control @error('batch_number') is-invalid @enderror"
                               placeholder="Номер партии"
                               name="batch_number"
                               value="{{ old('batch_number') }}"
                               required>
                    </div>
                    @error('batch_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Сумма входа (общая) --}}
                <div class="mb-3">
                    <label class="form-label" for="entry_price_total">Сумма вх. <span class="text-danger">*</span></label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text @error('entry_price_total') text-danger icon-border @enderror"><i class="bx bx-money"></i></span>
                        <input type="number" step="0.01" id="entry_price_total"
                               class="form-control @error('entry_price_total') is-invalid @enderror"
                               placeholder="Сумма вх."
                               name="entry_price_total"
                               value="{{ old('entry_price_total') }}"
                               required>
                    </div>
                    @error('entry_price_total')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Цена за единицу --}}
                <div class="mb-3">
                    <label class="form-label" for="entry_price_unit">Цена вх. за ед. <span class="text-danger">*</span></label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text @error('entry_price_unit') text-danger icon-border @enderror"><i class="bx bx-dollar-circle"></i></span>
                        <input type="number" step="0.01" id="entry_price_unit"
                               class="form-control @error('entry_price_unit') is-invalid @enderror"
                               placeholder="Цена вх. за единицу"
                               name="entry_price_unit"
                               value="{{ old('entry_price_unit') }}"
                               required>
                    </div>
                    @error('entry_price_unit')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Количество --}}
                <div class="mb-3">
                    <label class="form-label" for="entry_amount">Количество <span class="text-danger">*</span></label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text @error('entry_amount') text-danger icon-border @enderror"><i class="bx bx-cube"></i></span>
                        <input type="number" step="1" id="entry_amount"
                               class="form-control @error('entry_amount') is-invalid @enderror"
                               placeholder="Количество"
                               name="entry_amount"
                               value="{{ old('entry_amount') }}"
                               required>
                    </div>
                    @error('entry_amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>

        </div>
    </div>
@endsection
