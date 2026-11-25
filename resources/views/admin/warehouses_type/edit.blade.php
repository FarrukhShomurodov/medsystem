@extends('admin.layouts.app')

@section('title', 'Изменить тип склада')

@section('header')
    <x-page-header title="Изменить тип склада" :breadcrumbs="['Типы складов' => route('warehouses.type.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('warehouses.type.update', $type->id) }}">
            @csrf
            @method('PUT')

            <div class="card-body">
                @include('admin.warehouses_type._form', ['type' => $type])
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
