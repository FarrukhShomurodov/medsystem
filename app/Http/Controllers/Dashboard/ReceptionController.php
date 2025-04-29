<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Services\ReceptionService;
use App\Traits\HandlesStaffAvailability;
use Illuminate\Contracts\View\View;

class ReceptionController extends Controller
{
    use HandlesStaffAvailability;

    protected ReceptionService $service;

    public function __construct(ReceptionService $service)
    {
        $this->middleware(['permission:view_receptions'])->only('index');
        $this->middleware(['permission:create_receptions'])->only(['create', 'store']);
        $this->middleware(['permission:edit_receptions'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_receptions'])->only('destroy');

        $this->service = $service;
    }

    public function index(): View
    {
        $positions = StaffPosition::query()->get();

        return view('admin.receptions.index', compact('positions'));
    }

    public function doctorCalendar(Staff $staff): View
    {
        return view('admin.receptions.calendar', compact('staff'));
    }
}
