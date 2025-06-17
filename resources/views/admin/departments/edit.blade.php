@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Отделение</title>
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

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Редактировать</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('departments.update', $department->id) }}" method="POST"
                  enctype="multipart/form-data">
                @csrf
                @method('put')

                <div class="row">
                    <div class="col-md-6 mb-3">
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

                    <div class="col-md-6 mb-3">
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

                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="warehouse_id">Склады </label>

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

                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="rooms">Палаты <span
                                class="text-danger">*</span></label>

                        <select id="rooms" name="rooms[]"
                                class="form-control select2 @error('rooms') is-invalid @enderror" required
                                multiple>
                            @foreach($wardRooms as $wardRoom)
                                <option
                                    value="{{ $wardRoom->id }}" @selected(in_array($wardRoom->id, old('rooms', $department->rooms->pluck('id')->toArray())) )>
                                    {{ $wardRoom->room_number }} (Койки: {{ $wardRoom->beds->count() }})
                                </option>
                            @endforeach
                        </select>

                        @error('rooms')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                @php
                    $groupedByPosition = $department->staffs->groupBy(function($staff) {
                        return optional($staff->position)->name ?? 'Без должности';
                    });
                @endphp

                @foreach($groupedByPosition as $positionName => $staffs)
                    <div class="mb-4">
                        <h4 class="font-bold text-lg">{{ $positionName }}</h4>
                        <ul class="ml-4 list-disc">
                            @foreach($staffs as $staff)
                                <li>
                                    <a href="{{ route('staffs.show', $staff->id) }}">{{ $staff->staff->name }}  {{ $staff->staff->last_name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach


                <button type="submit" class="btn btn-warning ">Редактировать</button>
            </form>
        </div>
    </div>
@endsection
