<?php

namespace App\View\Components;

use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavItem;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * <x-sidebar />: the dashboard's vertical navigation, built from App\Support\Navigation\Navigation
 * for the signed-in account. An entry is listed when the account passes its permission check
 * (the same check as @can), and highlighted while one of its active route patterns matches.
 */
final class Sidebar extends Component
{
    public function __construct(
        private readonly Gate $gate,
        private readonly Request $request,
    ) {}

    public function render(): View
    {
        return view('components.sidebar', [
            'sections' => Navigation::visibleTo(fn (string $permission): bool => $this->gate->check($permission)),
        ]);
    }

    public function isActive(NavItem $item): bool
    {
        foreach ($item->links() as $link) {
            if ($this->request->routeIs(...$link->active)) {
                return true;
            }
        }

        return false;
    }
}
