@extends('admin.layouts.app')

@section('title', 'Изменить услугу')

@section('header')
    <x-page-header title="Изменить услугу" :breadcrumbs="['Услуги' => route('services.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('services.update', $service) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.services._form')
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
