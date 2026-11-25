<?php

namespace App\View\Components;

use App\Enums\Contracts\HasIcon;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * <x-status-icon :status="$prescription->status" />: a status as an icon, its label as the tooltip
 * and the name screen readers announce. Next to the printed label, pass decorative: the icon is
 * then hidden from screen readers, which would otherwise read the label twice.
 */
final class StatusIcon extends Component
{
    public function __construct(public HasIcon $status, public bool $decorative = false) {}

    public function render(): View
    {
        return view('components.status-icon');
    }
}
