@extends('admin.layouts.app')

@section('title', 'Изменить склад')

@section('header')
    <x-page-header title="Изменить склад" :breadcrumbs="['Склады' => route('warehouses.index')]" />
@endsection

@section('content')
    @include('admin.warehouses._form', ['warehouse' => $warehouse])
@endsection
