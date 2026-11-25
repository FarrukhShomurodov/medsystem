@extends('admin.layouts.app')
@use('App\Support\Display')

@section('title', 'Назначенные услуги')

@section('header')
    <x-page-header title="Назначенные услуги" pretitle="Услуги">
        @can('create_patient_services')
            <x-slot:actions>
                <a href="{{ route('patient.services.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Назначить услугу</a>
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
                    <th>Услуга</th>
                    <th>Пациент</th>
                    <th>Дата оказания услуги</th>
                    <th>Статус</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($services as $service)
                    <tr>
                        <td class="text-secondary">{{ $service->id }}</td>
                        <td>{{ $service->service->name }}</td>
                        <td>{{ $service->patient?->full_name ?? '—' }}</td>
                        <td class="text-nowrap">{{ Display::date($service->service_date) }}</td>
                        <td><x-status-badge :status="$service->status" /></td>
                        <td>
                            <div class="table-actions">
                                @can('edit_patient_services')
                                    <a href="{{ route('patient.services.edit', $service) }}" class="btn btn-sm">Изменить</a>
                                @endcan
                                {{-- A paid order is the record of the payment and is not deleted. --}}
                                @if (! $service->isPaid())
                                    @can('delete_patient_services')
                                        <x-delete-button :action="route('patient.services.destroy', $service)" confirm="Удалить назначенную услугу? Действие нельзя отменить." />
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state text="Назначенных услуг пока нет." /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($services->hasPages())
            <div class="card-footer">{{ $services->links() }}</div>
        @endif
    </div>
@endsection
