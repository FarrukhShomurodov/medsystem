@if ($decorative)
<i {{ $attributes->class([$status->icon()]) }} aria-hidden="true"></i>
@else
<i {{ $attributes->class([$status->icon()]) }} role="img" title="{{ $status->label() }}" aria-label="{{ $status->label() }}"></i>
@endif
