@extends('admin.layouts.app')

@section('title', 'Сестринское окно')

@section('header')
    <x-page-header title="Сестринское окно" pretitle="Работа с пациентами" />
@endsection

@section('content')
    @include('admin.sister_window.partials.patients', ['patients' => $patients, 'current' => null])
@endsection
