@extends('admin.layouts.app')

@section('title', 'Изменить расходный материал')

@section('header')
    <x-page-header title="Изменить расходный материал" :breadcrumbs="['Расходные материалы' => route('supplies.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('supplies.update', $supply->id) }}">
            @csrf
            @method('PUT')

            <div class="card-body">
                @include('admin.supplies._form', ['supply' => $supply])
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
