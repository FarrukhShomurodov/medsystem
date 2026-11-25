{{--
    What a list shows when it has nothing to show:

    @forelse ($regions as $region) ... @empty
        <tr><td colspan="3"><x-empty-state text="Добавьте первую область." /></td></tr>
    @endforelse

    The slot holds an optional action (a "Создать" link).
--}}
@props([
    'title' => 'Записей пока нет',
    'text' => null,
    'icon' => 'bx-folder-open',
])

<div {{ $attributes->class(['empty py-5']) }}>
    <div class="empty-icon" aria-hidden="true"><i class="bx {{ $icon }}"></i></div>
    <p class="empty-title">{{ $title }}</p>
    @if ($text)
        <p class="empty-subtitle text-secondary">{{ $text }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="empty-action">{{ $slot }}</div>
    @endif
</div>
