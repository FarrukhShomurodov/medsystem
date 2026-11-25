{{--
    A figure of the day at the top of the dashboard, the whole tile a link to its page.

    card:   App\Queries\Dashboard\DashboardCard      url:  the page it opens
    icon:   a Boxicons class                          tone: a Tabler colour (primary, green ...)
    label:  what the figure counts                    values: the figure, one line per value (amounts per currency)
    meta:   a short line under the figure
--}}
<div class="col-sm-6 col-xl">
    <a href="{{ $url }}" class="card card-sm card-link card-link-pop dashboard-tile" data-dashboard-card="{{ $card->value }}">
        <div class="card-body">
            <div class="d-flex align-items-start gap-3">
                <span class="avatar bg-{{ $tone }}-lt flex-shrink-0" aria-hidden="true"><i class="bx {{ $icon }}"></i></span>
                <div class="dashboard-shrink">
                    <div class="subheader">{{ $label }}</div>
                    @foreach ($values as $value)
                        <div class="h2 mb-0 dashboard-figure">{{ $value }}</div>
                    @endforeach
                    <div class="text-secondary small mt-1">{{ $meta }}</div>
                </div>
            </div>
        </div>
    </a>
</div>
