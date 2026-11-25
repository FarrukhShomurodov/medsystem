@extends('admin.layouts.app')

@section('title', 'Создать тип склада')

@section('header')
    <x-page-header title="Создать тип склада" :breadcrumbs="['Типы складов' => route('warehouses.type.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('warehouses.type.store') }}">
            @csrf

            <div class="card-body">
                @include('admin.warehouses_type._form', ['type' => null])
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
