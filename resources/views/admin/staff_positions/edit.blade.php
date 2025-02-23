@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Редактировать должность</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('staffs.position.index') }}">Должности</a> /</span>Редактировать
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
        </div>
        <div class="card-body">
            <form action="{{ route('staffs.position.update', $position->id) }}" method="POST"
                  enctype="multipart/form-data">
                @csrf
                @method('put')

                <div class="mb-3">
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
                               value="{{ old('name', $position->name) }}"
                               aria-describedby="basic-icon-default-company2" required/>
                    </div>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-warning ">Редактировать</button>
            </form>
        </div>
    </div>
@endsection
