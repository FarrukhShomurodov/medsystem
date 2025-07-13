@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Переходы</title>
@endsection

@section('content')
    <div class="alert-container position-fixed top-0 end-0 p-3" style="z-index: 10050;">
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <div class="alert alert-solid-danger alert-dismissible d-flex align-items-center" role="alert">
                    <i class="bx bx-error-circle fs-4 me-2"></i>
                    {{ $error }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endforeach
        @endif

        @if(session()->has('success'))
            <div class="alert alert-solid-success alert-dismissible d-flex align-items-center" role="alert">
                <i class="bx bx-check-circle fs-4 me-2"></i>
                {{ session()->get('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <div class="container mt-4">
        <h4>Переходы</h4>

        @foreach($requests as $request)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <div>
                        <strong>Склад:</strong> {{ $request->warehouse->name }} <br>
                        <strong>Дата:</strong> {{ $request->created_at->format('d.m.Y H:i') }}
                    </div>
                    <form action="{{ route('supply-requests.approve', $request->id) }}" method="POST">
                        @csrf
                        @if($request->status == 'pending')
                            <button type="submit" class="btn btn-success">
                                Принять заказ
                            </button>
                        @endif
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
