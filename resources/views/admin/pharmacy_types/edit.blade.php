@extends('admin.layouts.app')

@section('title', 'Изменить тип аптеки')

@section('header')
    <x-page-header title="Изменить тип аптеки" :breadcrumbs="['Типы аптек' => route('pharmacy.types.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('pharmacy.types.update', $type->id) }}">
            @csrf
            @method('PUT')

            <div class="card-body">
                @include('admin.pharmacy_types._form', ['type' => $type])
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
