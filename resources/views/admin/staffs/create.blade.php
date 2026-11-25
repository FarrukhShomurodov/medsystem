@extends('admin.layouts.app')

@section('title', 'Создать сотрудника')

@section('header')
    <x-page-header title="Создать сотрудника" :breadcrumbs="['Персонал' => route('staffs.index')]" />
@endsection

@section('content')
    @include('admin.staffs.form', ['staff' => null, 'action' => route('staffs.store')])
@endsection

@section('scripts')
    @vite('resources/js/pages/staff-form.js')
@endsection
