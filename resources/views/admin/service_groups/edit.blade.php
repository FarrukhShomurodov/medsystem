@extends('admin.layouts.app')

@section('title', 'Изменить группу услуг')

@section('header')
    <x-page-header title="Изменить группу услуг" :breadcrumbs="['Группы услуг' => route('service.groups.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('service.groups.update', $group) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required" for="name">Название</label>
                        <div class="input-group input-group-flat">
                            <span class="input-group-text"><i class="bx bx-rename" aria-hidden="true"></i></span>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                   placeholder="Название" value="{{ old('name', $group->name) }}" required>
                        </div>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <fieldset class="col-md-6" data-row-list data-row-list-next="{{ $group->subGroups->count() }}">
                        <legend class="form-label required">Подгруппы</legend>
                        <div class="vstack gap-2" data-row-list-items>
                            @foreach ($group->subGroups as $index => $subGroup)
                                @include('admin.service_groups._sub-group', [
                                    'idName' => "sub_groups[{$index}][id]",
                                    'name' => "sub_groups[{$index}][name]",
                                    'subGroup' => $subGroup,
                                ])
                            @endforeach
                        </div>
                        <template data-row-list-template>
                            @include('admin.service_groups._sub-group', [
                                'idName' => 'sub_groups[__INDEX__][id]',
                                'name' => 'sub_groups[__INDEX__][name]',
                                'subGroup' => null,
                            ])
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
