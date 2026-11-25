@extends('admin.layouts.app')

@php
    $fullName = $patient->full_name;
    $viewer = auth()->user();
@endphp

@section('title', "Изменить пациента: {$fullName}")

@section('header')
    <x-page-header title="Изменить пациента" :breadcrumbs="[
        'Пациенты' => $viewer?->can('list_patients') ? route('patients.index') : null,
        $fullName => $viewer?->can('view_patients') ? route('patients.show', $patient->id) : null,
    ]" />
@endsection

@section('content')
    @include('admin.patients.partials.form', ['patient' => $patient])
@endsection

@section('scripts')
    @vite('resources/js/pages/patient-form.js')
@endsection
