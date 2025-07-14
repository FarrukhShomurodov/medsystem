@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Склад - Остатки</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">Остатки</span>
    </h6>

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

    <div id="errors_alert"></div>

    <div class="card shadow-sm">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Остатки </h5>

            <form method="GET" class="d-flex align-items-center gap-2">
                <label for="type" class="form-label mb-0">Тип:</label>
                <select name="type" id="type" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="">Все</option>
                    <option value="medications" {{ request('type') == 'medications' ? 'selected' : '' }}>Медикаменты
                    </option>
                    <option value="supplies" {{ request('type') == 'supplies' ? 'selected' : '' }}>Мед. принадлежности
                    </option>
                    <option value="household" {{ request('type') == 'household' ? 'selected' : '' }}>Хоз. материалы
                    </option>
                </select>
            </form>

            <a href="{{ route('warehouse.order') }}" class="btn btn-primary "
               style="margin-right: 22px;">Заказать</a>
        </div>


        <div class="card-header">
            <ul class="d-flex justify-content-between align-items-center list-unstyled">
                <li class="mb-1"><span class="badge bg-danger">Красный</span> — срок годности истекает менее чем через
                    30 дней
                </li>
                <li class="mb-1"><span class="badge bg-warning text-dark">Жёлтый</span> — количество меньше 20</li>
                <li class="mb-1"><span class="badge bg-danger">Красный</span> (приоритет) — если оба условия совпадают
                </li>
            </ul>
        </div>

        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Тип</th>
                    <th>Название</th>
                    <th>Количество</th>
                    <th>Срок годности</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @php $index = 1; @endphp

                {{-- Медикаменты --}}
                @foreach ($medications as $item)
                    @php
                        $class = '';
                        $orderAble = false;

                        $expirationDate = \Carbon\Carbon::parse($item->expiration_date);
                        $isExpiringSoon = $expirationDate->lt(now()->addDays(30));
                        $isLowStock = $item->quantity < 20;

                        if ($isExpiringSoon && $isLowStock) {
                            $class = 'bg-danger text-white';
                        } elseif ($isExpiringSoon) {
                            $class = 'bg-danger text-white';
                        } elseif ($isLowStock) {
                            $orderAble = true;
                            $class = 'bg-warning text-dark';
                        }
                    @endphp
                    <tr class="{{ $class }}">
                        <td>{{ $index++ }}</td>
                        <td><span class="badge bg-info">Медикамент</span></td>
                        <td>{{ $item->medication->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->expiration_date }}</td>
                        <td>
                            @if($orderAble)
                                <button class="btn btn-sm btn-primary"
                                        onclick="window.location.href='{{ route('warehouse.order') }}'">
                                    Заказать
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach

                {{-- Расходники --}}
                @foreach ($supplies as $item)
                    @foreach ($item->warehouse as $warehouseItem)
                        @php
                            $class = '';
                            $orderAble = false;

                            $isLowStock = $warehouseItem->pivot->quantity < 20;

                            if ($isLowStock) {
                                $class = 'bg-warning text-dark';
                                $orderAble = true;
                            }
                        @endphp

                        <tr class="{{ $class }}">
                            <td>{{ $index++ }}</td>
                            <td><span class="badge bg-success">Расходник</span></td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $warehouseItem->pivot->quantity }}</td>
                            <td>-</td>
                            <td>
                                @if($orderAble)
                                    <button class="btn btn-sm btn-primary"
                                            onclick="window.location.href='{{ route('warehouse.order') }}'">
                                        Заказать
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach

                {{-- Хоз. материалы --}}
                @foreach($household as $item)
                    @foreach ($item->warehouse as $warehouseItem)
                        @php
                            $class = '';
                            $orderAble = false;

                            $isLowStock = $warehouseItem->pivot->quantity < 20;

                            if ($isLowStock) {
                                $class = 'bg-warning text-dark';
                                $orderAble = true;
                            }
                        @endphp

                        <tr class="{{ $class }}">
                            <td>{{ $index++ }}</td>
                            <td><span class="badge bg-warning">Хоз. материал</span></td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $warehouseItem->pivot->quantity }}</td>
                            <td>-</td>
                            <td>
                                @if($orderAble)
                                    <button class="btn btn-sm btn-primary"
                                            onclick="window.location.href='{{ route('warehouse.order') }}'">
                                        Заказать
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
