@extends('admin.layouts.app')

@section('title', 'Пользователи панели')

@section('header')
    <x-page-header title="Пользователи панели" pretitle="Организация">
        @can('create_admins')
            <x-slot:actions>
                <a href="{{ route('admins.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
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
                    <th>Email</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse($admins as $admin)
                    <tr>
                        <td>{{ $admin->email }}</td>
                        <td>
                            {{-- AdminPolicy: only a main administrator may change or delete a main administrator's account. --}}
                            <div class="table-actions">
                                @can('edit_admins')
                                    @can('update', $admin)
                                        <a href="{{ route('admins.edit', $admin) }}" class="btn btn-sm">Изменить</a>
                                    @endcan
                                @endcan
                                @unless($admin->is(auth('admin')->user()))
                                    @can('delete_admins')
                                        @can('delete', $admin)
                                            <x-delete-button :action="route('admins.destroy', $admin)"
                                                             :confirm="'Удалить учётную запись '.$admin->email.'? Действие нельзя отменить.'" />
                                        @endcan
                                    @endcan
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2"><x-empty-state text="Учётных записей панели пока нет." /></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($admins->hasPages())
            <div class="card-footer">{{ $admins->links() }}</div>
        @endif
    </div>
@endsection
