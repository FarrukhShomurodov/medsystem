@extends('admin.layouts.app')

@section('title', 'Создать услугу')

@section('header')
    <x-page-header title="Создать услугу" :breadcrumbs="['Услуги' => route('services.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('services.store') }}">
            @csrf
            <div class="card-body">
                @include('admin.services._form', ['service' => null])
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
