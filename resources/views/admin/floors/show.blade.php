@extends('admin.layouts.app')

@section('title', 'Этаж: '.$floor->name)

@section('header')
    <x-page-header :title="$floor->name" :breadcrumbs="['Этажи' => route('floors.index')]">
        @can('edit_floors')
            <x-slot:actions>
                <a href="{{ route('floors.edit', $floor) }}" class="btn">Изменить</a>
            </x-slot:actions>
        @endcan
    </x-page-header>
@endsection

@section('content')
    <div class="row row-cards">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Отделения</h2>
                    @can('create_departments')
                        <div class="card-actions">
                            <a href="{{ route('departments.create') }}" class="btn btn-sm btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Добавить отделение</a>
                        </div>
                    @endcan
                </div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <thead>
                        <tr>
                            <th class="w-1">#</th>
                            <th>Название</th>
                            <th>Склад</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($floor->departments as $department)
                            <tr>
                                <td class="text-secondary">{{ $loop->iteration }}</td>
                                <td>{{ $department->name }}</td>
                                <td>{{ $department->warehouse->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><x-empty-state title="Нет отделений на этом этаже" icon="bx-building-house" /></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Кабинеты</h2>
                    @can('create_offices')
                        <div class="card-actions">
                            <a href="{{ route('offices.create') }}" class="btn btn-sm btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Добавить кабинет</a>
                        </div>
                    @endcan
                </div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <thead>
                        <tr>
                            <th class="w-1">#</th>
                            <th>Название</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($floor->offices as $office)
                            <tr>
                                <td class="text-secondary">{{ $loop->iteration }}</td>
                                <td>{{ $office->name }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2"><x-empty-state title="Нет кабинетов на этом этаже" icon="bx-door-open" /></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
