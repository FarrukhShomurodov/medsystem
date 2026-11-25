@extends('admin.layouts.app')

@section('title', 'Медикаменты склада')

@section('header')
    <x-page-header title="Медикаменты склада" pretitle="Склад и аптека" />
@endsection

@section('content')
    <div class="card">
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                <tr>
                    <th class="w-1">#</th>
                    <th>Склад</th>
                    <th>Препарат</th>
                    <th class="text-end">Кол-во</th>
                    <th>Срок годности</th>
                    <th>Дата получения</th>
                    <th>Номер партии</th>
                    <th class="w-1"><span class="visually-hidden">Действия</span></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($accountings as $accounting)
                    @php
                        // The rules of the remains pages: a batch expires the day after its date, and
                        // its warning is due within its medication's notify_before_days.
                        $expired = $accounting->isExpired(today());
                        $expiring = ! $expired && $accounting->medication->expiryWarningDue($accounting->expiration_date, today());
                    @endphp
                    <tr @class(['table-danger' => $expired || $expiring, 'fw-semibold' => $expired])>
                        <td class="text-secondary">{{ $accounting->id }}</td>
                        <td>
                            @can('view_warehouses')
                                <a href="{{ route('warehouses.show', $accounting->warehouse_id) }}">{{ $accounting->warehouse->name }}</a>
                            @else
                                {{ $accounting->warehouse->name }}
                            @endcan
                        </td>
                        <td>
                            @can('view_medications')
                                <a href="{{ route('medications.show', $accounting->medication_id) }}">{{ $accounting->medication->name }}</a>
                            @else
                                {{ $accounting->medication->name }}
                            @endcan
                        </td>
                        <td class="text-end">{{ $accounting->quantity }}</td>
                        <td class="text-nowrap">
                            {{ $accounting->expiration_date->format('d.m.Y') }}
                            @if ($expired)
                                <span class="badge bg-danger text-white ms-1">просрочен</span>
                            @elseif ($expiring)
                                <span class="badge bg-danger-lt ms-1">истекает</span>
                            @endif
                        </td>
                        <td>{{ $accounting->date_of_receipt?->format('d.m.Y') ?? '—' }}</td>
                        <td>{{ $accounting->batch_number ?? '—' }}</td>
                        <td>
                            @can('view_warehouse_medication_detail')
                                <a href="{{ route('warehouse.medication.show', $accounting->id) }}" class="btn btn-sm">Просмотреть</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8"><x-empty-state title="На складе нет медикаментов" icon="bx-capsule" /></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($accountings->hasPages())
            <div class="card-footer">{{ $accountings->links() }}</div>
        @endif
    </div>
@endsection
