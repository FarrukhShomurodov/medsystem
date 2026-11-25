@extends('admin.layouts.app')

@section('title', 'Изменить производителя')

@section('header')
    <x-page-header title="Изменить производителя" :breadcrumbs="['Производители' => route('manufacturers.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('manufacturers.update', $manufacturer->id) }}">
            @csrf
            @method('PUT')

            <div class="card-body">
                @include('admin.manufacturers._form', ['manufacturer' => $manufacturer])
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
