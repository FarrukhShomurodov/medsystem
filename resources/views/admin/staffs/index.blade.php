@extends('admin.layouts.app')

@section('title', 'Персонал')

@section('header')
    <x-page-header title="Персонал" pretitle="Организация">
        @can('create_staffs')
            <x-slot:actions>
                <a href="{{ route('staffs.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
            </x-slot:actions>
        @endcan
    </x-page-header>
@endsection

@section('content')
    <div class="card">
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                <tr>
                    <th class="w-1">#</th>
                    <th>Имя</th>
                    <th>Фамилия</th>
                    <th>Должность</th>
                    <th>Статус</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse($staffs as $staff)
                    {{-- The form edits the employee's login too: AdminPolicy guards a main administrator's account. --}}
                    @php($account = $staff->admin)
                    <tr>
                        <td class="text-secondary">{{ $staff->id }}</td>
                        <td><a href="{{ route('staffs.show', $staff) }}">{{ $staff->name }}</a></td>
                        <td>{{ $staff->last_name }}</td>
                        <td>{{ $staff->professionalData?->position?->name }}</td>
                        <td>
                            @if($workingToday->contains($staff->id))
                                <span class="badge bg-success-lt">На работе</span>
                            @else
                                <span class="badge bg-danger-lt">Не работает</span>
                            @endif
                        </td>
                        <td>
                            <div class="table-actions">
                                @can('edit_staffs')
                                    @if($account === null || auth('admin')->user()->can('update', $account))
                                        <a href="{{ route('staffs.edit', $staff) }}" class="btn btn-sm">Изменить</a>
                                    @endif
                                @endcan
                                @can('delete_staffs')
                                    @if($account === null || auth('admin')->user()->can('delete', $account))
                                        <x-delete-button :action="route('staffs.destroy', $staff)"
                                                         :confirm="'Удалить сотрудника '.$staff->full_name.'?'.($account ? ' Его учётная запись для входа тоже будет удалена.' : '').' Действие нельзя отменить.'" />
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6"><x-empty-state text="Добавьте первого сотрудника." /></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($staffs->hasPages())
            <div class="card-footer">{{ $staffs->links() }}</div>
        @endif
    </div>
@endsection
