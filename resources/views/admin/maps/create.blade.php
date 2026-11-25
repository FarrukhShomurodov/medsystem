@extends('admin.layouts.app')

@php
    $fullName = $patient->full_name;
@endphp

@section('title', 'Создать карту обследования')

@section('header')
    <x-page-header title="Создать карту обследования" :breadcrumbs="[
        'Пациенты' => auth()->user()?->can('list_patients') ? route('patients.index') : null,
        $fullName => auth()->user()?->can('view_patients') ? route('patients.show', $patient->id) : null,
    ]" />
@endsection

@section('content')
    @include('admin.maps.partials.form', ['map' => null, 'patientId' => $patient->id])
@endsection

@section('scripts')
    @vite('resources/js/pages/map-form.js')
@endsection
