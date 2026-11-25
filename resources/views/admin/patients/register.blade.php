{{-- The short registration form of the front desk (PatientController::create). --}}
@extends('admin.layouts.app')

@section('title', 'Создать пациента')

@section('header')
    <x-page-header title="Создать пациента" :breadcrumbs="['Пациенты' => auth()->user()?->can('list_patients') ? route('patients.index') : null]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('patients.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="row g-3">
                    @include('admin.patients.partials.basic-fields', ['patient' => null])
                    @include('admin.patients.partials.field', ['field' => [
                        'name' => 'phone', 'label' => 'Телефон', 'icon' => 'bx-phone', 'required' => true, 'col' => 'col-sm-4',
                        'placeholder' => '+998 (XX) XXX XX XX', 'value' => old('phone'),
                        'attributes' => ['inputmode' => 'tel', 'autocomplete' => 'tel'],
                    ]])
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary"><i class="bx bx-save" aria-hidden="true"></i>Сохранить</button>
            </div>
        </form>
    </div>
@endsection
