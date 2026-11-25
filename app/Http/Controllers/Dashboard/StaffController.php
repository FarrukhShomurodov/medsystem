<?php

namespace App\Http\Controllers\Dashboard;

use App\Exceptions\Scheduling\AccountException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StaffRequest;
use App\Models\Admin;
use App\Models\DayOfWeek;
use App\Models\Department;
use App\Models\Reception;
use App\Models\Service;
use App\Models\Specialty;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\Survey;
use App\Services\Scheduling\ReceptionService;
use App\Services\StaffService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class StaffController extends Controller implements HasMiddleware
{
    /** How many appointments the warning after a schedule change names; the rest are counted. */
    private const int LISTED_APPOINTMENTS = 10;

    public function __construct(
        private readonly StaffService $service,
        private readonly ReceptionService $receptions,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_staffs', only: ['index', 'show']),
            new Middleware('permission:create_staffs', only: ['create', 'store']),
            new Middleware('permission:edit_staffs', only: ['edit', 'update']),
            new Middleware('permission:delete_staffs', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $staffs = Staff::query()
            ->select(['id', 'name', 'last_name', 'middle_name'])
            // The rows offer editing and deleting by the login's AdminPolicy, which reads its roles.
            ->with(['professionalData.position:id,name', 'admin:id,staff_id', 'admin.roles'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        $workingToday = Staff::query()
            ->whereKey($staffs->pluck('id'))
            ->workingOn(today())
            ->pluck('id');

        return view('admin.staffs.index', compact('staffs', 'workingToday'));
    }

    public function show(Staff $staff): View
    {
        $staff->load([
            'admin:id,staff_id,email',
            'contact',
            'document',
            'professionalData.position',
            'professionalData.additionPosition',
            'professionalData.speciality',
            'professionalData.department',
            'services:id,name',
        ]);

        $isWorking = $staff->isWorkingOn(today());

        return view('admin.staffs.show', compact('staff', 'isWorking'));
    }

    public function create(): View
    {
        return view('admin.staffs.create', $this->formOptions());
    }

    public function store(StaffRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('staffs.index')->with('success', 'Сотрудник успешно добавлен!');
    }

    public function edit(Staff $staff): View
    {
        $staff->load(['admin:id,staff_id,email', 'contact', 'document', 'professionalData', 'services:id', 'surveys:id', 'schedule', 'absence']);

        if ($staff->admin !== null) {
            Gate::authorize('update', $staff->admin);
        }

        // Schedule rows by weekday and type, so the form does not query once per day.
        $schedule = $staff->schedule->groupBy(['day_of_week_id', 'type']);

        return view('admin.staffs.edit', [...$this->formOptions(), 'staff' => $staff, 'schedule' => $schedule]);
    }

    public function update(StaffRequest $request, Staff $staff, #[CurrentUser(Admin::GUARD)] Admin $actor): RedirectResponse
    {
        try {
            $account = $this->service->update($staff, $request->validated())->admin;
        } catch (AccountException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        // As on the admins screen: an employee who changes their own password stays signed in here.
        if ($account?->is($actor)) {
            Auth::guard(Admin::GUARD)->setUser($account);
        }

        $redirect = to_route('staffs.index')->with('success', 'Сотрудник успешно обновлён!');
        $uncovered = $this->receptions->uncoveredAppointmentsOf($staff);

        return $uncovered->isEmpty() ? $redirect : $redirect->with('warning', $this->uncoveredWarning($uncovered));
    }

    /**
     * @param  Collection<int, Reception>  $appointments
     */
    private function uncoveredWarning(Collection $appointments): string
    {
        $listed = $appointments->take(self::LISTED_APPOINTMENTS)->map(fn (Reception $reception): string => sprintf(
            '%s %s, %s',
            Carbon::parse($reception->date)->format('d.m.Y'),
            substr($reception->start_time, 0, 5),
            ($reception->patient ?? $reception->map?->patient)->full_name ?? 'пациент не указан',
        ));
        $more = $appointments->count() - $listed->count();

        return 'Записи вне графика сотрудника, перенесите или отмените их: '.$listed->implode('; ')
            .($more > 0 ? "; и ещё {$more}." : '.');
    }

    public function destroy(Staff $staff, #[CurrentUser(Admin::GUARD)] Admin $actor): RedirectResponse
    {
        if ($staff->admin !== null) {
            Gate::authorize('delete', $staff->admin);
        }

        try {
            $this->service->destroy($staff, $actor);
        } catch (AccountException $e) {
            return back()->with('error', $e->getMessage());
        }

        return to_route('staffs.index')->with('success', 'Сотрудник успешно удалён!');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'positions' => StaffPosition::query()->orderBy('id')->get(),
            'specialties' => Specialty::query()->orderBy('name')->get(),
            'departments' => Department::query()->orderBy('name')->get(),
            'services' => Service::query()->where('is_active', true)->orderBy('name')->get(),
            'surveys' => Survey::query()->get(),
            'days' => DayOfWeek::query()->orderBy('id')->get(),
        ];
    }
}
