<?php

namespace App\View\Components;

use App\Enums\Contracts\HasBadge;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * <x-status-badge :status="$transfer->status" />: a status as a coloured badge with its label.
 */
final class StatusBadge extends Component
{
    public function __construct(public HasBadge $status) {}

    public function render(): View
    {
        return view('components.status-badge');
    }
}
