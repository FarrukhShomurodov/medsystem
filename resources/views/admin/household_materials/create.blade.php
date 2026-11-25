@extends('admin.layouts.app')

@section('title', 'Создать хозяйственный материал')

@section('header')
    <x-page-header title="Создать хозяйственный материал" :breadcrumbs="['Хозяйственные материалы' => route('household.materials.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('household.materials.store') }}">
            @csrf

            <div class="card-body">
                @include('admin.household_materials._form', ['material' => null])
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
