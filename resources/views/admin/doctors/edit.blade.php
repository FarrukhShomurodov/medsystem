@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Редактировать врача</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('doctors.index') }}">Врачи</a> /</span>Редактировать
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
            <form action="{{ route('doctors.update', $doctor->id) }}" method="POST"
                  enctype="multipart/form-data">
                @csrf
                @method('put')

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="basic-icon-default-name">Имя <span
                                class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-name2"
                                  class="input-group-text @error('first_name') text-danger icon-border @enderror">
                                <i class="bx bx-user"></i>
                            </span>
                            <input type="text" id="basic-icon-default-name"
                                   class="form-control @error('first_name') is-invalid @enderror"
                                   placeholder="Имя" aria-label="Имя" name="first_name"
                                   value="{{ old('first_name', $doctor->first_name) }}"
                                   aria-describedby="basic-icon-default-name2" required/>
                        </div>
                        @error('first_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="basic-icon-default-last-name">Фамилия <span
                                class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                        <span id="basic-icon-default-last-name2"
                              class="input-group-text @error('last_name') text-danger icon-border @enderror">
                            <i class="bx bx-user-circle"></i>
                        </span>
                            <input type="text" id="basic-icon-default-last-name"
                                   class="form-control @error('last_name') is-invalid @enderror"
                                   placeholder="Фамилия" aria-label="Фамилия" name="last_name"
                                   value="{{ old('last_name', $doctor->last_name) }}"
                                   aria-describedby="basic-icon-default-last-name2"
                                   required/>
                        </div>
                        @error('last_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="basic-icon-default-middle-name">Отчество</label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-middle-name2"
                                  class="input-group-text @error('middle_name') text-danger icon-border @enderror">
                                <i class="bx bx-id-card"></i>
                            </span>
                            <input type="text" id="basic-icon-default-middle-name"
                                   class="form-control @error('middle_name') is-invalid @enderror"
                                   placeholder="Отчество" aria-label="Отчество" name="middle_name"
                                   value="{{ old('middle_name', $doctor->middle_name) }}"
                                   aria-describedby="basic-icon-default-middle-name2"/>
                        </div>
                        @error('middle_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="specialty">Специальность <span
                            class="text-danger">*</span></label>

                    <select name="specialty_id"
                            class="select2 form-control @error('specialty_id') is-invalid @enderror"
                            id="specialty"
                            required>
                        <option value="" disabled selected>Выберите специальность</option>

                        @foreach($specialties as $specialty)
                            <option
                                value="{{ $specialty->id }}" @selected($specialty->id == old('specialty_id', $doctor->specialty_id))> {{ $specialty->name }} </option>
                        @endforeach
                    </select>

                    @error('specialty_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-warning ">Редактировать</button>
            </form>
        </div>
    </div>
@endsection
