@extends('admin.layouts.app')

@section('title', 'Создать препарат')

@section('header')
    <x-page-header title="Создать препарат" :breadcrumbs="['Препараты' => route('medications.index')]" />
@endsection

@section('content')
    @include('admin.medications._form', ['medication' => null])
@endsection
