@extends('admin.layouts.app')

@section('title', 'Назначить услугу')

@section('header')
    <x-page-header title="Назначить услугу" :breadcrumbs="['Назначенные услуги' => route('patient.services.index')]" />
@endsection

@section('content')
    <div class="bs-stepper wizard-vertical vertical">
        @include('admin.patient_services._steps')

        <div class="bs-stepper-content">
            <form method="POST" action="{{ route('patient.services.store') }}">
                @csrf
                @include('admin.patient_services._form', ['order' => null, 'catalogue' => $catalogue, 'maps' => []])
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    @vite('resources/js/pages/patient-service-form.js')
@endsection
