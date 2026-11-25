@extends('admin.layouts.app')

@section('title', 'Изменить перевод №'.$transfer->id)

@section('header')
    <x-page-header title="Изменить перевод" :breadcrumbs="['Переводы' => route('transfers.index'), 'Перевод №'.$transfer->id => null]" />
@endsection

@section('content')
    <div class="card">
        <form action="{{ route('transfers.update', $transfer) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card-body">
                @include('admin.transfers._form', [
                    'lines' => old('medications', $transfer->medications
                        ->map(fn ($line) => ['medication_id' => $line->medication_id, 'quantity' => $line->quantity])
                        ->all()),
                ])
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>
@endsection
