<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Reception;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Services\ReceptionService;
use App\Traits\HandlesStaffAvailability;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReceptionController extends Controller
{
    use HandlesStaffAvailability;

    protected ReceptionService $service;

    public function __construct(ReceptionService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): View
    {
        $roles = auth('admin')->user()->getRoleNames();

        if ($roles->contains('Доктор')) {
            $staffId = auth('admin')->user()->staff_id;
        } else {
            $staffId = $request->filled('staff_id');
        }

        $receptions = Reception::query()
            ->when($request->filled('staff_id') || $staffId, function ($q) use ($staffId) {
                $q->where('staff_id', $staffId);
            })
            ->when($request->filled('is_disposable'), function ($q) use ($request) {
                if ($request->input('is_disposable') == 'yes') {
                    $q->where('examination_map_id', null);
                }
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        $staffs = Staff::query()->select('id', 'name')->get();

        return view('admin.receptions.index', compact('receptions', 'staffs'));
    }

    public function appointment(): View
    {
        $roles = auth('admin')->user()->getRoleNames();

        if ($roles->contains('Доктор')) {
            $staff = Staff::find(auth('admin')->user()->staff_id);
            $positions = $staff->professionalData->position()->get();
        } else {
            $positions = StaffPosition::query()->get();
        }

        return view('admin.receptions.appointment ', compact('positions'));
    }

    public function doctorCalendar(Staff $staff)
    {
        if (!$staff->services()->where('service_type_id', 1)->exists()) {
            return redirect()->back()->withErrors('Календарь не доступень!');
        }

        return view('admin.receptions.calendar', compact('staff'));
    }
}
