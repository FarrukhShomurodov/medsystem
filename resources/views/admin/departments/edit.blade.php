@extends('admin.layouts.app')

@section('title', 'Изменить отделение')

@section('header')
    <x-page-header title="Изменить отделение" :breadcrumbs="['Отделения' => route('departments.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('departments.update', $department) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.departments._form')
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit">Сохранить</button>
            </div>
        </form>
    </div>

    @if ($department->staffs->isNotEmpty())
        <div class="card mt-3">
            <div class="card-header">
                <h2 class="card-title">Сотрудники отделения</h2>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach ($department->staffs->groupBy(fn ($staff) => $staff->position?->name ?? 'Без должности') as $positionName => $staffs)
                        <div class="col-md-6 col-lg-4">
                            <h3 class="fw-bold fs-3 mb-2">{{ $positionName }}</h3>
                            <ul class="list-unstyled mb-0">
                                @foreach ($staffs as $staff)
                                    <li>
                                        @can('list_staffs')
                                            <a href="{{ route('staffs.show', $staff->staff_id) }}">{{ $staff->staff?->full_name }}</a>
                                        @else
                                            {{ $staff->staff?->full_name }}
                                        @endcan
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endsection
