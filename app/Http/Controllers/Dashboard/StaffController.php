<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffRequest;
use App\Models\Department;
use App\Models\Service;
use App\Models\Specialty;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\Survey;
use App\Services\StaffService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use PHPUnit\TextUI\XmlConfiguration\MoveWhitelistExcludesToCoverage;

class StaffController extends Controller
{
    protected StaffService $service;

    public function __construct(StaffService $service)
    {
        $this->middleware(['permission:view_staffs'])->only(['index', 'show']);
        $this->middleware(['permission:create_staffs'])->only(['create', 'store']);
        $this->middleware(['permission:edit_staffs'])->only(['edit', 'update']);
        $this->middleware(['permission:delete_staffs'])->only('destroy');

        $this->service = $service;
    }

    public function index(): View
    {
        $staffs = Staff::query()
            ->select(['id', 'name', 'last_name'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.staffs.index', compact('staffs'));
    }

    public function show(Staff $staff): View
    {
        return view('admin.staffs.show', compact('staff'));
    }

    public function create(): View
    {
        $positions = StaffPosition::query()->get();
        $specialties = Specialty::query()->get();
        $departments = Department::query()->get();
        $services = Service::query()->where('is_active', true)->get();
        $surveys = Survey::query()->get();


        return view('admin.staffs.create', compact('specialties', 'positions', 'departments', 'services', 'surveys'));
    }

    public function store(StaffRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->store((array)$validated);

        return redirect()->route('staffs.index')->with('success', 'Персонал успешно добавлен!');
    }

    public function edit(Staff $staff): View
    {
        $positions = StaffPosition::query()->get();
        $specialties = Specialty::query()->get();
        $departments = Department::query()->get();
        $services = Service::query()->where('is_active', true)->get();
        $surveys = Survey::query()->get();

        return view('admin.staffs.edit', compact('staff', 'specialties', 'positions', 'departments', 'services', 'surveys'));
    }

    public function update(StaffRequest $request, Staff $staff): RedirectResponse
    {
        $validated = $request->validated();
        $this->service->update($staff, (array)$validated);

        return redirect()->route('staffs.index')->with('success', 'Персонал успешно обновлен!');
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        $this->service->destroy($staff);

        return redirect()->route('staffs.index')->with('success', 'Персонал успешно удален!');
    }
}
