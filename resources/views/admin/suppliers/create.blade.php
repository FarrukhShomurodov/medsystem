@extends('admin.layouts.app')

@section('title', 'Создать поставщика')

@section('header')
    <x-page-header title="Создать поставщика" :breadcrumbs="['Поставщики' => route('suppliers.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('suppliers.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="card-body">
                @include('admin.suppliers._form', ['supplier' => null])
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
