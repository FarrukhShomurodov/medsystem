@extends('admin.layouts.app')

@section('title', 'Изменить палату')

@section('header')
    <x-page-header title="Изменить палату" :breadcrumbs="['Палаты' => route('departments.room.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('departments.room.update', $room) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required" for="room_number">Номер палаты</label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text"><i class="bx bx-rename" aria-hidden="true"></i></span>
                            <input type="text" id="room_number" name="room_number"
                                   class="form-control @error('room_number') is-invalid @enderror"
                                   placeholder="Номер палаты" value="{{ old('room_number', $room->room_number) }}" required>
                        </div>
                        @error('room_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <fieldset class="col-md-6" data-row-list data-row-list-next="{{ $room->beds->count() }}">
                        <legend class="form-label required">Койки</legend>
                        <div class="vstack gap-2" data-row-list-items>
                            @foreach ($room->beds as $index => $bed)
                                @include('admin.department_rooms._bed', [
                                    'idName' => "bed_numbers[{$index}][id]",
                                    'name' => "bed_numbers[{$index}][bed_number]",
                                    'bed' => $bed,
                                ])
                            @endforeach
                        </div>
                        <template data-row-list-template>
                            @include('admin.department_rooms._bed', [
                                'idName' => 'bed_numbers[__INDEX__][id]',
                                'name' => 'bed_numbers[__INDEX__][bed_number]',
                                'bed' => null,
                            ])
                        </template>
                        @error('bed_numbers')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @error('bed_numbers.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <button type="button" class="btn btn-outline-primary mt-2" data-row-add><i class="bx bx-plus" aria-hidden="true"></i>Добавить койку</button>
                    </fieldset>
                </div>

                @if ($room->beds->isNotEmpty())
                    <h2 class="h4 mt-4 mb-0">Схема палаты</h2>
                    <div class="room-map">
                        @foreach ($room->beds as $bed)
                            <div class="bed-item">
                                <i class="bx bx-bed fs-1" aria-hidden="true"></i>
                                <span>Койка {{ $bed->bed_number }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
