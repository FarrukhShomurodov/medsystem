@extends('admin.layouts.app')

@section('title', 'Изменить назначенную услугу')

@section('header')
    <x-page-header title="Изменить назначенную услугу" :breadcrumbs="['Назначенные услуги' => route('patient.services.index')]" />
@endsection

@section('content')
    <div class="bs-stepper wizard-vertical vertical">
        @include('admin.patient_services._steps')

        <div class="bs-stepper-content">
            <form method="POST" action="{{ route('patient.services.update', $service) }}">
                @csrf
                @method('PUT')
                @include('admin.patient_services._form', ['order' => $service, 'catalogue' => $catalogue, 'maps' => $maps])
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    @vite('resources/js/pages/patient-service-form.js')
@endsection
