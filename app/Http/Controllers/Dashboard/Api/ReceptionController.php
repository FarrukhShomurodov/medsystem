<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Enums\ReceptionStatus;
use App\Exceptions\Scheduling\ReceptionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReceptionRequest;
use App\Http\Requests\Scheduling\ReceptionStatusRequest;
use App\Models\Admin;
use App\Models\Reception;
use App\Models\Service;
use App\Models\Staff;
use App\Services\Scheduling\ReceptionService;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

/**
 * Authorisation lives on the routes (routes/ajax.php): the permission of the calling page, and
 * ReceptionPolicy for an appointment (show, changeStatus). A booking names its employee in the
 * body, so ReceptionRequest asks the policy; staffByService() offers only the calendars it allows.
 */
class ReceptionController extends Controller
{
    public function __construct(private readonly ReceptionService $service) {}

    /**
     * The employed doctors who can be booked for the service, while it is on the price list; staff
     * without an appointment length are left out, the calendars cannot draw a grid for them. Only
     * the calendars the viewer works on are offered (ReceptionPolicy): a doctor's account gets the
     * doctor alone, so the grid draws no colleague's column whose slots it could not book.
     */
    public function staffByService(Service $service, #[CurrentUser(Admin::GUARD)] Admin $viewer): JsonResponse
    {
        $staff = Staff::query()
            ->employed()
            ->whereHas('services', fn (Builder $services) => $services
                ->whereKey($service->id)
                ->where('is_appointment', true)
                ->where('is_active', true))
            ->whereRelation('professionalData', 'admission_period', '>', 0)
            ->with('professionalData:id,staff_id,admission_period')
            ->orderBy('last_name')
            ->get(['id', 'name', 'last_name', 'middle_name'])
            ->filter(fn (Staff $doctor): bool => $viewer->can('workOnCalendar', [Reception::class, $doctor]))
            ->values();

        return response()->json($staff->map(fn (Staff $doctor): array => [
            'id' => $doctor->id,
            'full_name' => $doctor->full_name,
            'admission_period' => $doctor->slotMinutes(),
        ]));
    }

    /**
     * The appointment as the calendars' dialog prints it, and nothing more: both calendar
     * permissions reach it without view_patients, so the patient card, the stay and the doctor's
     * records stay out of it. Only the name columns are read. An older appointment may name its
     * patient only through the stay (receptions.patient_id is nullable).
     */
    public function show(Reception $reception): JsonResponse
    {
        $reception->load([
            'patient:id,first_name,last_name,middle_name',
            'map:id,patient_id',
            'map.patient:id,first_name,last_name,middle_name',
            'staff:id,name,last_name,middle_name',
            'staff.professionalData:id,staff_id,position_id',
            'staff.professionalData.position:id,name',
        ]);
        $status = $reception->status;

        return response()->json([
            'id' => $reception->id,
            'date' => $reception->date,
            'start_time' => $reception->start_time,
            'end_time' => $reception->end_time,
            'comment' => $reception->comment,
            'status' => $status->value,
            'status_label' => $status->label(),
            'can_cancel' => $status->canTransitionTo(ReceptionStatus::Cancelled),
            'patient_id' => $reception->patient_id,
            'patient_name' => ($reception->patient ?? $reception->map?->patient)?->full_name,
            'staff_name' => $reception->staff?->full_name,
            'position' => $reception->staff?->professionalData?->position?->name,
        ]);
    }

    public function store(ReceptionRequest $request): JsonResponse
    {
        try {
            $reception = $this->service->book($request->booking());
        } catch (ReceptionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($reception, 201);
    }

    public function changeStatus(Reception $reception, ReceptionStatusRequest $request): JsonResponse
    {
        try {
            $reception = $this->service->changeStatus($reception, $request->status());
        } catch (ReceptionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($reception);
    }
}
