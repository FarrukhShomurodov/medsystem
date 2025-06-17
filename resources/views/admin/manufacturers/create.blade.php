@extends('admin.layouts.app')

@section('title')
    <title> MED ERA | Создать производителя</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('manufacturers.index') }}">Производители</a> /</span>Создать
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
            <form action="{{ route('manufacturers.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label" for="basic-icon-default-name">Название <span
                                class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-name2"
                                  class="input-group-text @error('name') text-danger icon-border @enderror">
                                <i class="bx bx-rename"></i>
                            </span>
                            <input type="text" id="basic-icon-default-name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="Название" aria-label="Название" name="name" value="{{ old('name') }}"
                                   aria-describedby="basic-icon-default-name2" required/>
                        </div>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-6">
                        <label class="form-label" for="basic-icon-default-country_of_manufacture">Страна производства <span
                                class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-country_of_manufacture"
                                  class="input-group-text @error('country_of_manufacture') text-danger icon-border @enderror">
                                <i class="bx bx-world"></i>
                            </span>
                            <input type="text" id="basic-icon-default-country_of_manufacture"
                                   class="form-control @error('country_of_manufacture') is-invalid @enderror"
                                   placeholder="Страна производства" aria-label="Страна производства" name="country_of_manufacture" value="{{ old('country_of_manufacture') }}"
                                   aria-describedby="basic-icon-default-name2" required/>
                        </div>
                        @error('country_of_manufacture')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>
    </div>
@endsection
