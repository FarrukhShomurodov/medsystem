@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Медикаменты склада</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">Медикаменты склада</span>
    </h6>

    <div class="card shadow-sm">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Медикаменты склада </h5>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Склад</th>
                    <th>Препарат</th>
                    <th>Кол-во</th>
                    <th>Срок годности</th>
                    <th>Дата получения</th>
                    <th>Номер партии</th>
                </tr>
                </thead>
                <tbody>
                @foreach($accountings as $accounting)
                    @php
                        $expirationDate = \Carbon\Carbon::parse($accounting->expiration_date);
                        $daysUntilExpiration = now()->diffInDays($expirationDate, false);

                        $rowClass = '';
                        if ($daysUntilExpiration <= 10 && $daysUntilExpiration > 0) {
                            $rowClass = 'bg-warning text-white'; // Желтый цвет (скоро истекает)
                        } elseif ($daysUntilExpiration <= 0) {
                            $rowClass = 'bg-danger text-white'; // Красный цвет (уже просрочено)
                        }
                    @endphp
                    <tr class="{{ $rowClass }}">
                        <td>{{ $accounting->id }}</td>
                        <td>{{ $accounting->warehouse->name }}</td>
                        <td>{{ $accounting->medication->name }}</td>
                        <td>{{ $accounting->quantity }}</td>
                        <td>{{ $accounting->expiration_date }}</td>
                        <td>{{ $accounting->date_of_receipt }}</td>
                        <td>{{ $accounting->batch_number }}</td>
                    </tr>
                @endforeach
            </table>
            <div class="mt-4 p-1">
                {{ $accountings->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
