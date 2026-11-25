@extends('admin.layouts.app')

@php($pageTitle = 'Препараты: '.$pharmacy->name)

@section('title', $pageTitle)

@section('header')
    <x-page-header :title="$pageTitle" :breadcrumbs="['Аптеки' => route('pharmacies.index')]" />
@endsection

@section('content')
    <div class="card">
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
                @forelse ($medications as $medication)
                    <tr>
                        <td class="text-secondary">{{ $medication->id }}</td>
                        <td>{{ $medication->name }}</td>
                        <td class="text-end">{{ $medication->pivot->quantity ?? 0 }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3"><x-empty-state title="В аптеке нет препаратов" icon="bx-capsule" /></td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
