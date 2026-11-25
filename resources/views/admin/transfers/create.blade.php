@extends('admin.layouts.app')

@section('title', 'Создать перевод')

@section('header')
    <x-page-header title="Создать перевод" :breadcrumbs="['Переводы' => route('transfers.index')]" />
@endsection

@section('content')
    <div class="card">
        <form action="{{ route('transfers.store') }}" method="POST">
            @csrf

            <div class="card-body">
                @include('admin.transfers._form', [
                    'transfer' => null,
                    'lines' => old('medications', [['medication_id' => null, 'quantity' => null]]),
                ])
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
