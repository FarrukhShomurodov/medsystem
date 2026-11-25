@extends('admin.layouts.app')

@section('title', 'Создать группу препаратов')

@section('header')
    <x-page-header title="Создать группу препаратов" :breadcrumbs="['Группы препаратов' => route('medication.groups.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('medication.groups.store') }}">
            @csrf

            <div class="card-body">
                @include('admin.medication_groups._form', ['group' => null])
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
