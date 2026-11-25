@extends('admin.layouts.app')

@section('title', 'Создать склад')

@section('header')
    <x-page-header title="Создать склад" :breadcrumbs="['Склады' => route('warehouses.index')]" />
@endsection

@section('content')
    @include('admin.warehouses._form', ['warehouse' => null])
@endsection
