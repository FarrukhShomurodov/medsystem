@extends('admin.layouts.app')

@section('title', 'Изменить поставщика')

@section('header')
    <x-page-header title="Изменить поставщика" :breadcrumbs="['Поставщики' => route('suppliers.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('suppliers.update', $supplier->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card-body">
                @include('admin.suppliers._form', ['supplier' => $supplier])
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
