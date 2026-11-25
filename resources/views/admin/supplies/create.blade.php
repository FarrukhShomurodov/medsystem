@extends('admin.layouts.app')

@section('title', 'Создать расходный материал')

@section('header')
    <x-page-header title="Создать расходный материал" :breadcrumbs="['Расходные материалы' => route('supplies.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('supplies.store') }}">
            @csrf

            <div class="card-body">
                @include('admin.supplies._form', ['supply' => null])
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
