@extends('admin.layouts.app')

@section('title', 'Пациенты')

@php
    $filtered = request()->anyFilled(['id', 'first_name', 'second_name', 'middle_name', 'birth_date', 'department_id', 'staff_id']);
@endphp

@section('header')
    <x-page-header title="Пациенты" pretitle="Работа с пациентами">
        <x-slot:actions>
            <button class="btn" type="button" data-bs-toggle="collapse" data-bs-target="#patient-filters"
                    aria-expanded="{{ $filtered ? 'true' : 'false' }}" aria-controls="patient-filters">
                <i class="bx bx-filter-alt" aria-hidden="true"></i>Фильтры
            </button>
            @can('create_patients')
                <a href="{{ route('patients.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
            @endcan
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    <div class="card">
        <div id="patient-filters" class="collapse @if ($filtered) show @endif">
            <div class="card-body border-bottom">
                @include('admin.patients.partials.filters', ['departments' => $departments, 'doctors' => $staffs])
            </div>
        </div>

        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                @include('admin.patients.partials.list-head')
                </thead>
                <tbody>
                @forelse ($patients as $patient)
                    @include('admin.patients.partials.list-row', ['patient' => $patient, 'number' => $patient->id, 'current' => false])
                @empty
                    <tr>
                        <td colspan="9">
                            <x-empty-state :title="$filtered ? 'Никого не нашли' : 'Пациентов пока нет'"
                                           :text="$filtered ? 'Измените условия поиска.' : null" icon="bx-user" />
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($patients->hasPages())
            <div class="card-footer">{{ $patients->links() }}</div>
        @endif
    </div>
@endsection
