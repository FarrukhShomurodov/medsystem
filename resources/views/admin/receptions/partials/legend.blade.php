{{--
    The colours of the booking grid's cells (resources/scss/pages/_scheduling.scss, .slot-*).
    @include('admin.receptions.partials.legend', ['unavailable' => true]) adds the cells of the
    weekly calendar that today's grid does not draw: unavailable and past slots.
--}}
<div class="slot-legend" role="list" aria-label="Обозначения">
    <span class="slot-legend-item" role="listitem"><span class="slot-swatch slot-free" aria-hidden="true"></span>Свободно</span>
    <span class="slot-legend-item" role="listitem"><span class="slot-swatch slot-booked" aria-hidden="true"></span>Забронировано</span>
    @if($unavailable)
        <span class="slot-legend-item" role="listitem"><span class="slot-swatch slot-unavailable" aria-hidden="true"></span>Недоступно</span>
        <span class="slot-legend-item" role="listitem"><span class="slot-swatch slot-past" aria-hidden="true"></span>Прошедшая дата</span>
    @endif
</div>
