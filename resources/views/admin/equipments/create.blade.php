@extends('admin.layouts.app')

@section('title', 'Создать оборудование')

@section('header')
    <x-page-header title="Создать оборудование" :breadcrumbs="['Оборудование' => route('equipment.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('equipment.store') }}">
            @csrf
            <div class="card-body">
                @include('admin.equipments._form', ['equipment' => null])
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
