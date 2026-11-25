{{--
    What the stock needs someone to act on: one line per figure the account may see, each a link to its page.
--}}
@use('App\Queries\Dashboard\DashboardCard')

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Склад</h2>
    </div>

    <div class="list-group list-group-flush">
        @if ($dashboard->pendingSupplyRequests !== null)
            @include('admin.dashboard.stock-line', [
                'card' => DashboardCard::SupplyRequests,
                'url' => route('supply-requests.index'),
                'icon' => 'bx-transfer',
                'tone' => $dashboard->pendingSupplyRequests > 0 ? 'orange' : 'secondary',
                'label' => 'Заказы отделений',
                'meta' => 'ждут решения общего склада',
                'figure' => $dashboard->pendingSupplyRequests,
            ])
        @endif

        @foreach ([
            [DashboardCard::ExpiringMainStock, $dashboard->mainWarehouseExpiry, 'main.warehouse.remains', 'общий склад'],
            [DashboardCard::ExpiringWardStock, $dashboard->wardWarehouseExpiry, 'warehouse.remains', 'склады отделений'],
        ] as [$card, $expiry, $route, $place])
            @if ($expiry !== null)
                @include('admin.dashboard.stock-line', [
                    'card' => $card,
                    'url' => route($route, ['type' => 'medications']),
                    'icon' => 'bx-time-five',
                    'tone' => match (true) {
                        $expiry->expired > 0 => 'red',
                        $expiry->due > 0 => 'orange',
                        default => 'secondary',
                    },
                    'label' => 'Истекает срок годности',
                    'meta' => $place.' · '.($expiry->expired > 0 ? 'из них просрочено: '.$expiry->expired : 'просроченных нет'),
                    'figure' => $expiry->due,
                ])
            @endif
        @endforeach

        @if ($dashboard->outOfStock !== null)
            @include('admin.dashboard.stock-line', [
                'card' => DashboardCard::OutOfStock,
                'url' => route('main.warehouse.receipts.goods.index'),
                'icon' => 'bx-package',
                'tone' => $dashboard->outOfStock > 0 ? 'red' : 'secondary',
                'label' => 'Нет в наличии',
                'meta' => 'позиции общего склада с нулевым остатком',
                'figure' => $dashboard->outOfStock,
            ])
        @endif
    </div>
</div>
