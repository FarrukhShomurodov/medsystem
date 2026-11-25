@extends('admin.layouts.app')

@section('title', 'Создать аптеку')

@section('header')
    <x-page-header title="Создать аптеку" :breadcrumbs="['Аптеки' => route('pharmacies.index')]" />
@endsection

@section('content')
    @include('admin.pharmacies._form', ['pharmacy' => null])
@endsection
