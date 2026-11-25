@extends('admin.layouts.app')

@section('title', 'Изменить препарат')

@section('header')
    <x-page-header title="Изменить препарат" :breadcrumbs="['Препараты' => route('medications.index')]" />
@endsection

@section('content')
    @include('admin.medications._form', ['medication' => $medication])
@endsection
