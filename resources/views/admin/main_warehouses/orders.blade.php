@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Подтверждение заказов</title>
@endsection

@section('content')
    <div class="container mt-4">
        <h4>Ожидающие заказы</h4>

        @foreach($requests as $request)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <div>
                        <strong>Склад:</strong> {{ $request->warehouse->name }} <br>
                        <strong>Дата:</strong> {{ $request->created_at->format('d.m.Y H:i') }}
                    </div>
                    <form action="{{ route('supply-requests.approve', $request->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">
                           Принять заказ
                        </button>
                    </form>
                </div>

                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="table-light">
                        <tr>
                            <th>Тип</th>
                            <th>Наименование</th>
                            <th>Количество</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($request->items as $item)
                            <tr>
                                <td>
                                    @switch($item->type)
                                        @case('medication') 🧪 Медикамент @break
                                        @case('medical_supply') 💉 Расходник @break
                                        @case('household_material') 🧼 Хоз. товар @break
                                        @default ❓ Неизвестно
                                   @endswitch
                                </td>
                                <td>{{ $item->supplyable->name ?? '—' }}</td>
                                <td>{{ $item->requested_quantity }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        @if($requests->isEmpty())
            <div class="alert alert-info">Нет заказов в ожидании.</div>
        @endif
    </div>
@endsection
