@extends('admin.layouts.app')

@section('title', 'Изменить оборудование')

@section('header')
    <x-page-header title="Изменить оборудование" :breadcrumbs="['Оборудование' => route('equipment.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('equipment.update', $equipment) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.equipments._form')
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
