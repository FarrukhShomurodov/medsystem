@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Редактировать оборудование</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('equipment.index') }}">Оборудование</a> /</span>Редактировать
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
            <form action="{{ route('equipment.update', $equipment->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Название <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $equipment->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Тип оборудования <span class="text-danger">*</span></label>
                        <select name="equipment_type_id" class="form-control @error('equipment_type_id') is-invalid @enderror" required>
                            @foreach($equipmentTypes as $type)
                                <option value="{{ $type->id }}" @selected(old('equipment_type_id', $equipment->equipment_type_id) == $type->id)>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('equipment_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label">Производитель <span class="text-danger">*</span></label>
                        <select name="manufacturer_id" class="form-control @error('manufacturer_id') is-invalid @enderror" required>
                            @foreach($manufacturers as $manufacturer)
                                <option value="{{ $manufacturer->id }}" @selected(old('manufacturer_id', $equipment->manufacturer_id) == $manufacturer->id)>
                                    {{ $manufacturer->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('manufacturer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Серийный номер <span class="text-danger">*</span></label>
                        <input type="text" name="serial_number" class="form-control @error('serial_number') is-invalid @enderror" value="{{ old('serial_number', $equipment->serial_number) }}" required>
                        @error('serial_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label">Цена <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $equipment->price) }}" required>
                        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Дата покупки <span class="text-danger">*</span></label>
                        <input type="text" name="date_purchase" class="form-control flatpickr-date @error('date_purchase') is-invalid @enderror" value="{{ old('date_purchase', $equipment->date_purchase) }}" placeholder="YYYY-MM-DD" required>
                        @error('date_purchase')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label">Дата калибровки <span class="text-danger">*</span></label>
                        <input type="text" name="calibration_date" class="form-control flatpickr-date @error('calibration_date') is-invalid @enderror" value="{{ old('calibration_date', $equipment->calibration_date) }}" placeholder="YYYY-MM-DD" required>
                        @error('calibration_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Дата срока службы <span class="text-danger">*</span></label>
                        <input type="text" name="term_date" class="form-control flatpickr-date @error('term_date') is-invalid @enderror" value="{{ old('term_date', $equipment->term_date) }}" placeholder="YYYY-MM-DD" required>
                        @error('term_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label">Оповестить за (дней) <span class="text-danger">*</span></label>
                        <input type="number" name="term_notify_days" class="form-control @error('term_notify_days') is-invalid @enderror" value="{{ old('term_notify_days', $equipment->term_notify_days) }}" required>
                        @error('term_notify_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Ближайшее обслуживание через (дней) <span class="text-danger">*</span></label>
                        <input type="number" name="upcoming_service_at" class="form-control @error('upcoming_service_at') is-invalid @enderror" value="{{ old('upcoming_service_at', $equipment->upcoming_service_at) }}" required>
                        @error('upcoming_service_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label">Ремонт <span class="text-danger">*</span></label>
                        <input type="text" name="repair" class="form-control @error('repair') is-invalid @enderror" value="{{ old('repair', $equipment->repair) }}" required>
                        @error('repair')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label class="form-label">Местоположение <span class="text-danger">*</span></label>
                        <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $equipment->location) }}" required>
                        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3 col-md-6">
                        <label class="form-label">Ответственный сотрудник <span class="text-danger">*</span></label>
                        <select name="staff_id" class="form-control @error('staff_id') is-invalid @enderror" required>
                            @foreach($staffList as $staff)
                                <option value="{{ $staff->id }}" @selected(old('staff_id', $equipment->staff_id) == $staff->id)>
                                    {{ $staff->name }} {{ $staff->last_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('staff_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Дата последней проверки <span class="text-danger">*</span></label>
                    <input type="text" name="last_inspection" class="form-control flatpickr-date @error('last_inspection') is-invalid @enderror" value="{{ old('last_inspection', $equipment->last_inspection) }}" placeholder="YYYY-MM-DD" required>
                    @error('last_inspection')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Статус</label>
                    <select name="status" class="form-control @error('status') is-invalid @enderror">
                        @foreach(\App\Models\Equipment::STATUS as $status)
                            <option value="{{ $status }}" @selected(old('status', $equipment->status) == $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-primary">Обновить</button>
            </form>
        </div>
    </div>
@endsection
