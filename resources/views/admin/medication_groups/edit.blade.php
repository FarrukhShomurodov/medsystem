@extends('admin.layouts.app')

@section('title', 'Изменить группу препаратов')

@section('header')
    <x-page-header title="Изменить группу препаратов" :breadcrumbs="['Группы препаратов' => route('medication.groups.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('medication.groups.update', $group->id) }}">
            @csrf
            @method('PUT')

            <div class="card-body">
                @include('admin.medication_groups._form', ['group' => $group])
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
