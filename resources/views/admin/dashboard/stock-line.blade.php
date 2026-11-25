{{--
    One line of the stock card, the whole line a link to its page.

    card: App\Queries\Dashboard\DashboardCard    url, icon, tone (a Tabler colour), label, meta, figure
--}}
<a href="{{ $url }}" class="list-group-item list-group-item-action" data-dashboard-card="{{ $card->value }}">
    <div class="d-flex align-items-center gap-3">
        <span class="avatar avatar-sm bg-{{ $tone }}-lt flex-shrink-0" aria-hidden="true"><i class="bx {{ $icon }}"></i></span>
        <div class="flex-fill dashboard-shrink">
            <div class="text-body">{{ $label }}</div>
            <div class="text-secondary small">{{ $meta }}</div>
        </div>
        <span class="h3 mb-0 dashboard-figure">{{ $figure }}</span>
    </div>
</a>
