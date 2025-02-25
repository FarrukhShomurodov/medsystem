@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Редактировать обородувание</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('departments.equipment.index') }}">Обородувание</a> /</span>Редактировать
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
            <form action="{{ route('departments.equipment.update', $equipment->id) }}" method="POST"
                  enctype="multipart/form-data">
                @csrf
                @method('put')

                <div class="row">
                    <div class="mb-3 col-6">
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
                                   value="{{ old('name', $equipment->name) }}"
                                   aria-describedby="basic-icon-default-company2" required/>
                        </div>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 col-6">
                        <label class="form-label" for="status">Статус <span
                                    class="text-danger">*</span></label>

                        <select name="status"
                                class="select2 form-control @error('status') is-invalid @enderror"
                                id="status"
                                required>
                            @foreach(\App\Models\Equipment::STATUS as $status)
                                <option
                                        value="{{ $status }}" @selected($status == old('region_id', $equipment->status))> {{ $status }} </option>
                            @endforeach
                        </select>

                        @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="basic-icon-last_inspection">Дата последней проверки <span
                                class="text-danger">*</span></label>
                    <div class="input-group input-group-merge">
                                <span id="basic-icon-birth-date2"
                                      class="input-group-text @error('last_inspection') text-danger icon-border @enderror">
                                    <i class="bx bx-calendar"></i>
                                </span>
                        <input type="text" name="last_inspection"
                               value="{{ old('last_inspection', $equipment->last_inspection) }}"
                               id="basic-icon-last_inspection"
                               class="form-control flatpickr-date @error('last_inspection') is-invalid @enderror"
                               placeholder="YYYY-MM-DD" aria-describedby="basic-icon-birth-date2" required/>
                    </div>
                    @error('last_inspection')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-warning ">Редактировать</button>
            </form>
        </div>
    </div>
@endsection
