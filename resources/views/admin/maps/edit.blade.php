@extends('admin.layouts.app')

@section('title', "Изменить карту обследования №{$map->id}")

@section('header')
    <x-page-header title="Изменить карту обследования" :breadcrumbs="[
        'Пациенты' => auth()->user()?->can('list_patients') ? route('patients.index') : null,
        'Карточка пациента' => auth()->user()?->can('view_patients') ? route('patients.show', $map->patient_id) : null,
        'Карта №'.$map->id => auth()->user()?->canAny(['view_patients', 'edit_patients']) ? route('maps.show', $map->id) : null,
    ]" />
@endsection

@section('content')
    @include('admin.maps.partials.form', ['map' => $map, 'patientId' => $map->patient_id])
@endsection

@section('scripts')
    @vite('resources/js/pages/map-form.js')
@endsection
