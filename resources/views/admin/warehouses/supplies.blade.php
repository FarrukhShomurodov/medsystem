@extends('admin.layouts.app')

@section('title', 'Расходные материалы склада')

@section('header')
    <x-page-header title="Расходные материалы склада" pretitle="Склад и аптека" />
@endsection

@section('content')
    <div class="card">
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                <tr>
                    <th class="w-1">#</th>
                    <th>Склад</th>
                    <th>Материал</th>
                    <th class="text-end">Кол-во</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($supplies as $row)
                    <tr>
                        <td class="text-secondary">{{ $row->id }}</td>
                        <td>
                            @can('view_warehouses')
                                <a href="{{ route('warehouses.show', $row->warehouse_id) }}">{{ $row->warehouse->name }}</a>
                            @else
                                {{ $row->warehouse->name }}
                            @endcan
                        </td>
                        <td>{{ $row->supply->name }}</td>
                        <td class="text-end">{{ $row->quantity }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4"><x-empty-state title="На складе нет расходных материалов" icon="bx-package" /></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($supplies->hasPages())
            <div class="card-footer">{{ $supplies->links() }}</div>
        @endif
    </div>
@endsection
