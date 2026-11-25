@extends('admin.layouts.app')

@section('title', 'Изменить должность: '.$position->name)

@section('header')
    <x-page-header title="Изменить должность" :breadcrumbs="['Должности' => route('staffs.position.index'), $position->name => null]" />
@endsection

@section('content')
    <div class="card">
        <form action="{{ route('staffs.position.update', $position) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.staff_positions.form', ['position' => $position, 'selected' => old('permissions', $selectedPermissions)])
            <div class="card-footer">
                <div class="btn-list justify-content-end">
                    <a href="{{ route('staffs.position.index') }}" class="btn">Отмена</a>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    @vite('resources/js/pages/staff-position-form.js')
@endsection
