@extends('admin.layouts.app')

@section('title', 'Заказы отделений')

@section('header')
    <x-page-header title="Заказы отделений" pretitle="Склад и аптека" />
@endsection

@section('content')
    @forelse ($requests as $request)
        @php($status = $request->status)
        <div class="card mb-3">
            <div class="card-header flex-wrap align-items-start gap-3">
                <div>
                    <h2 class="card-title">Заказ №{{ $request->id }} <x-status-badge :status="$status" class="ms-2" /></h2>
                    <div class="text-secondary small mt-1">
                        <strong>Склад:</strong> {{ $request->warehouse->name }}
                        <span class="mx-1" aria-hidden="true">·</span>
                        <strong>Дата:</strong> {{ $request->created_at->format('d.m.Y H:i') }}
                    </div>
                    @if ($request->rejection_reason)
                        <div class="small mt-1"><strong>Причина отказа:</strong> {{ $request->rejection_reason }}</div>
                    @endif
                </div>
                @can('approve_supply_requests')
                    @unless ($status->isFinal())
                        <div class="card-actions d-flex flex-wrap align-items-start gap-2">
                            <form action="{{ route('supply-requests.approve', $request) }}" method="POST"
                                  data-confirm="Принять заказ? Товары будут перемещены на склад отделения." data-confirm-button="Принять">
                                @csrf
                                <button type="submit" class="btn btn-success text-nowrap"><i class="bx bx-check" aria-hidden="true"></i>Принять заказ</button>
                            </form>
                            <form action="{{ route('supply-requests.reject', $request) }}" method="POST" class="d-flex gap-2"
                                  data-confirm="Отклонить заказ? Товары не будут перемещены." data-confirm-button="Отклонить">
                                @csrf
                                <label class="visually-hidden" for="reason-{{ $request->id }}">Причина отказа</label>
                                <input type="text" id="reason-{{ $request->id }}" name="reason" class="form-control" maxlength="1000"
                                       placeholder="Причина отказа">
                                <button type="submit" class="btn btn-outline-danger text-nowrap">Отклонить</button>
                            </form>
                        </div>
                    @endunless
                @endcan
            </div>

            <div class="table-responsive">
                <table class="table card-table table-vcenter">
                    <thead>
                    <tr>
                        <th>Тип</th>
                        <th>Наименование</th>
                        <th class="text-end">Заказано</th>
                        <th class="text-end">Отпущено</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($request->items as $item)
                        <tr>
                            <td>{{ $item->kindLabel() }}</td>
                            <td>{{ $item->supplyable?->name ?? '—' }}</td>
                            <td class="text-end">{{ $item->requested_quantity }}</td>
                            <td class="text-end">{{ $item->approved_quantity ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="card">
            <x-empty-state title="Заказов пока нет" text="Здесь появятся заявки складов отделений на общий склад." icon="bx-cart" />
        </div>
    @endforelse

    @if ($requests->hasPages())
        <div class="mt-3">{{ $requests->links() }}</div>
    @endif
@endsection
