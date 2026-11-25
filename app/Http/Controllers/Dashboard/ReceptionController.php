<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\ReceptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Scheduling\ReceptionFilterRequest;
use App\Models\Admin;
use App\Models\Reception;
use App\Models\Service;
use App\Models\Staff;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;

/**
 * Permissions are declared on the routes (routes/web.php), and so is ReceptionPolicy on the
 * doctor's calendar: a doctor's account opens only its own.
 */
class ReceptionController extends Controller
{
    /**
     * Doctors see their own appointments; everyone else may filter by employee. A day's list (the
     * dashboard's cards open it on their day) runs in the order of the day.
     */
    public function index(ReceptionFilterRequest $request, #[CurrentUser(Admin::GUARD)] Admin $viewer): View
    {
        $doctor = $viewer->doctorProfile();
        $staffId = $doctor->id ?? $request->staffId();
        $day = $request->day();

        $receptions = Reception::query()
            ->with(['patient:id,first_name,last_name,middle_name', 'service:id,name', 'staff:id,name,last_name,middle_name'])
            ->when($staffId, fn (Builder $query) => $query->where('staff_id', $staffId))
            // "Disposable" appointments are outpatient visits that belong to no examination map.
            ->when($request->disposable() === true, fn (Builder $query) => $query->whereNull('examination_map_id'))
            ->when($request->disposable() === false, fn (Builder $query) => $query->whereNotNull('examination_map_id'))
            ->when($request->status(), fn (Builder $query, ReceptionStatus $status) => $query->where('status', $status))
            ->when(
                $day,
                fn (Builder $query, CarbonImmutable $day) => $query->where('date', $day->toDateString())->orderBy('start_time')->orderBy('id'),
                fn (Builder $query) => $query->latest('id'),
            )
            ->paginate(10)
            ->withQueryString();

        $staffs = $doctor
            ? collect()
            : Staff::query()->orderBy('last_name')->orderBy('name')->get(['id', 'name', 'last_name', 'middle_name']);

        return view('admin.receptions.index', compact('receptions', 'staffs', 'doctor'));
    }

    /**
     * A doctor books appointments for their own services only. A service taken off the price list
     * (is_active off) is not booked any more.
     */
    public function appointment(#[CurrentUser(Admin::GUARD)] Admin $viewer): View
    {
        $doctor = $viewer->doctorProfile();

        $services = ($doctor?->services() ?? Service::query())
            ->where('is_appointment', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.receptions.appointment', compact('services'));
    }

    public function doctorCalendar(Staff $staff, Service $service): View|RedirectResponse
    {
        if (! $service->is_active) {
            return to_route('receptions.appointment')->with('error', 'Услуга снята с прайс-листа: на неё не записывают.');
        }

        // The booking grid links only the employees who offer the service. Any other pair comes from a
        // typed URL, and ReceptionService would refuse every slot its calendar offered.
        if (! $staff->services()->whereKey($service->id)->where('is_appointment', true)->exists()) {
            return to_route('receptions.appointment')->with('error', 'Сотрудник не ведёт приём по этой услуге: календарь недоступен.');
        }

        $staff->load('professionalData.position', 'professionalData.speciality');

        if ($staff->slotMinutes() === null) {
            return to_route('receptions.appointment')
                ->with('error', 'У сотрудника не указан период приёма: календарь недоступен.');
        }

        $weekStart = today()->startOfWeek(CarbonInterface::MONDAY)->toDateString();

        return view('admin.receptions.calendar', compact('staff', 'service', 'weekStart'));
    }
}
