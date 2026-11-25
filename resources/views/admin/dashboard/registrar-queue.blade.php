{{--
    The registrar's queue: today's orders waiting for confirmation, and the patients with most of them,
    each linking to the registration block of the patient's card.

    queue: App\Queries\Billing\RegistrarQueue
--}}
@use('App\Queries\Dashboard\DashboardCard')

<div class="card" data-dashboard-card="{{ DashboardCard::RegistrarQueue->value }}">
    <div class="card-header">
        <div>
            <h2 class="card-title">Ждут регистратуру</h2>
            <div class="card-subtitle mb-0">Заказы на сегодня без подтверждения</div>
        </div>
    </div>

    @if ($queue->orders === 0)
        <x-empty-state title="Всё подтверждено" icon="bx-check-circle"
                       text="На сегодня нет заказов, ожидающих регистратуру." />
    @else
        <div class="card-body border-bottom">
            <div class="d-flex align-items-baseline gap-2">
                <span class="h1 mb-0 dashboard-figure">{{ $queue->orders }}</span>
                <span class="text-secondary">
                    {{ trans_choice('заказ|заказа|заказов', $queue->orders) }}
                    у {{ $queue->patients }} {{ trans_choice('пациента|пациентов|пациентов', $queue->patients) }}
                </span>
            </div>
        </div>

        <div class="list-group list-group-flush">
            @foreach ($queue->first as $entry)
                <a href="{{ route('patients.show', $entry['patient']) }}#registration" class="list-group-item list-group-item-action">
                    <div class="d-flex align-items-center gap-2">
                        <span class="flex-fill text-truncate dashboard-shrink">{{ $entry['patient']->full_name }}</span>
                        <span class="text-secondary text-nowrap dashboard-figure">
                            {{ $entry['orders'] }} {{ trans_choice('заказ|заказа|заказов', $entry['orders']) }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        @if ($queue->patients > count($queue->first))
            <div class="card-footer text-secondary small">
                И ещё {{ $queue->patients - count($queue->first) }} {{ trans_choice('пациент|пациента|пациентов', $queue->patients - count($queue->first)) }}.
            </div>
        @endif
    @endif
</div>
