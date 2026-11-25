@extends('admin.layouts.app')

@section('title', 'Изменить аптеку')

@section('header')
    <x-page-header title="Изменить аптеку" :breadcrumbs="['Аптеки' => route('pharmacies.index')]" />
@endsection

@section('content')
    @include('admin.pharmacies._form', ['pharmacy' => $pharmacy])
@endsection
