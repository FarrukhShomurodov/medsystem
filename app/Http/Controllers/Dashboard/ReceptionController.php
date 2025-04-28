<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Staff;
use App\Models\StaffPosition;
use App\Services\ReceptionService;
use App\Traits\HandlesStaffAvailability;
use Illuminate\Contracts\View\View;

class ReceptionController
{
    use HandlesStaffAvailability;

    protected ReceptionService $service;

    public function __construct(ReceptionService $service)
    {
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
