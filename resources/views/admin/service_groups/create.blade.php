@extends('admin.layouts.app')

@section('title', 'Создать группу услуг')

@section('header')
    <x-page-header title="Создать группу услуг" :breadcrumbs="['Группы услуг' => route('service.groups.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('service.groups.store') }}">
            @csrf
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required" for="name">Название</label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text"><i class="bx bx-rename" aria-hidden="true"></i></span>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                   placeholder="Название" value="{{ old('name') }}" required>
                        </div>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <fieldset class="col-md-6" data-row-list>
                        <legend class="form-label required">Подгруппы</legend>
                        <div class="vstack gap-2" data-row-list-items>
                            @include('admin.service_groups._sub-group', ['name' => 'sub_groups[]', 'subGroup' => null])
                        </div>
                        <template data-row-list-template>
                            @include('admin.service_groups._sub-group', ['name' => 'sub_groups[]', 'subGroup' => null])
                        </template>
                        @error('sub_groups')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @error('sub_groups.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <button type="button" class="btn btn-outline-primary mt-2" data-row-add><i class="bx bx-plus" aria-hidden="true"></i>Добавить подгруппу</button>
                    </fieldset>
                </div>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
