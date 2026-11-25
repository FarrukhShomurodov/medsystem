@extends('admin.layouts.app')

@section('title', 'Создать производителя')

@section('header')
    <x-page-header title="Создать производителя" :breadcrumbs="['Производители' => route('manufacturers.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('manufacturers.store') }}">
            @csrf

            <div class="card-body">
                @include('admin.manufacturers._form', ['manufacturer' => null])
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
