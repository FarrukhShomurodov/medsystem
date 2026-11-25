@extends('admin.layouts.app')

@section('title', 'Изменить тип оборудования')

@section('header')
    <x-page-header title="Изменить тип оборудования" :breadcrumbs="['Типы оборудования' => route('equipment.types.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('equipment.types.update', $type) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required" for="name">Название</label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text"><i class="bx bx-rename" aria-hidden="true"></i></span>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                   placeholder="Название" value="{{ old('name', $type->name) }}" required>
                        </div>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
