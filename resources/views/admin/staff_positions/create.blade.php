@extends('admin.layouts.app')

@section('title', 'Создать должность')

@section('header')
    <x-page-header title="Создать должность" :breadcrumbs="['Должности' => route('staffs.position.index')]" />
@endsection

@section('content')
    <div class="card">
        <form action="{{ route('staffs.position.store') }}" method="POST">
            @csrf
            @include('admin.staff_positions.form', ['position' => null, 'selected' => old('permissions', [])])
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
