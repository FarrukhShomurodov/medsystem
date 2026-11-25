@extends('admin.layouts.app')

@section('title', 'Создать пациента')

@section('header')
    <x-page-header title="Создать пациента" :breadcrumbs="['Пациенты' => auth()->user()?->can('list_patients') ? route('patients.index') : null]" />
@endsection

@section('content')
    @include('admin.patients.partials.form', ['patient' => null])
@endsection

@section('scripts')
    @vite('resources/js/pages/patient-form.js')
@endsection
