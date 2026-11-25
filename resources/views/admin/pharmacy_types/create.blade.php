@extends('admin.layouts.app')

@section('title', 'Создать тип аптеки')

@section('header')
    <x-page-header title="Создать тип аптеки" :breadcrumbs="['Типы аптек' => route('pharmacy.types.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('pharmacy.types.store') }}">
            @csrf

            <div class="card-body">
                @include('admin.pharmacy_types._form', ['type' => null])
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
