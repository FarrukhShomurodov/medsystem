@extends('admin.layouts.app')

@section('title', 'Изменить сотрудника: '.$staff->full_name)

@section('header')
    <x-page-header title="Изменить сотрудника" :breadcrumbs="[
        'Персонал' => route('staffs.index'),
        $staff->full_name => auth()->user()?->can('list_staffs') ? route('staffs.show', $staff) : null,
    ]" />
@endsection

@section('content')
    @include('admin.staffs.form', ['staff' => $staff, 'action' => route('staffs.update', $staff)])
@endsection

@section('scripts')
    @vite('resources/js/pages/staff-form.js')
@endsection
