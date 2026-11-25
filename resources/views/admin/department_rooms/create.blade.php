@extends('admin.layouts.app')

@section('title', 'Создать палату')

@section('header')
    <x-page-header title="Создать палату" :breadcrumbs="['Палаты' => route('departments.room.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('departments.room.store') }}">
            @csrf
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required" for="room_number">Номер палаты</label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text"><i class="bx bx-rename" aria-hidden="true"></i></span>
                            <input type="text" id="room_number" name="room_number"
                                   class="form-control @error('room_number') is-invalid @enderror"
                                   placeholder="Номер палаты" value="{{ old('room_number') }}" required>
                        </div>
                        @error('room_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <fieldset class="col-md-6" data-row-list>
                        <legend class="form-label required">Койки</legend>
                        <div class="vstack gap-2" data-row-list-items>
                            @include('admin.department_rooms._bed', ['name' => 'bed_numbers[]', 'bed' => null])
                        </div>
                        <template data-row-list-template>
                            @include('admin.department_rooms._bed', ['name' => 'bed_numbers[]', 'bed' => null])
                        </template>
                        @error('bed_numbers')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @error('bed_numbers.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <button type="button" class="btn btn-outline-primary mt-2" data-row-add><i class="bx bx-plus" aria-hidden="true"></i>Добавить койку</button>
                    </fieldset>
                </div>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
