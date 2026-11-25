<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Queries\Dashboard\DashboardSummary;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /** The home page: the figures of the day the account may see (App\Queries\Dashboard\DashboardCard). */
    public function index(DashboardSummary $summary, #[CurrentUser(Admin::GUARD)] Admin $viewer): View
    {
        return view('admin.dashboard', [
            'dashboard' => $summary->for($viewer, today()->toImmutable()),
        ]);
    }
}
