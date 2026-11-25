@extends('admin.layouts.app')

@section('title', 'Изменить медицинское устройство')

@section('header')
    <x-page-header title="Изменить медицинское устройство" :breadcrumbs="['Медицинские устройства' => route('devices.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('devices.update', $device->id) }}">
            @csrf
            @method('PUT')

            <div class="card-body">
                @include('admin.devices._form', ['device' => $device])
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
