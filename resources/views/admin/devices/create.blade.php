@extends('admin.layouts.app')

@section('title', 'Создать медицинское устройство')

@section('header')
    <x-page-header title="Создать медицинское устройство" :breadcrumbs="['Медицинские устройства' => route('devices.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('devices.store') }}">
            @csrf

            <div class="card-body">
                @include('admin.devices._form', ['device' => null])
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
