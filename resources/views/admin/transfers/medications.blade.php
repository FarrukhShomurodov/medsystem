@extends('admin.layouts.app')

@section('title', 'Препараты перевода №'.$transfer->id)

@section('header')
    <x-page-header :title="'Перевод №'.$transfer->id" :breadcrumbs="['Переводы' => route('transfers.index')]">
        <x-slot:actions>
            <x-status-badge :status="$transfer->status" />
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Препараты</h2>
        </div>
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                <tr>
                    <th class="w-1">#</th>
                    <th>Препарат</th>
                    <th class="text-end">Кол-во</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($medications as $line)
                    <tr>
                        <td class="text-secondary">{{ $loop->iteration }}</td>
                        <td>{{ $line->medication->name }}</td>
                        <td class="text-end">{{ $line->quantity }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3"><x-empty-state title="В переводе нет препаратов" icon="bx-capsule" /></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
