<?php

namespace App\Http\Controllers\Dashboard\Api;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class StaffController
{
    public function surveys(Staff $staff): JsonResponse
    {
        return response()->json($staff->surveys->map(function ($survey) {
            return [
                'id' => $survey->id,
                'title' => $survey->title,
            ];
        }));
    }

    public function availabilityWeek(Request $request, $doctorId)
    {
        $doctor = Staff::findOrFail($doctorId);
        $startDate = Carbon::parse($request->query('start'));
        $endDate = $startDate->copy()->addDays(6);

        $result = [];

        for ($date = $startDate; $date <= $endDate; $date->addDay()) {
            if (!$doctor->isWorkingToday($date)) {
                continue;
            }

            $availability = $doctor->getTodayAvailability($date);
            $bookings = $doctor->getBookedTime($date);

            foreach ($availability as $avail) {
                $slot = [
                    'date' => $date->format('Y-m-d'),
                    'time' => $avail['time'],
                    'is_available' => $avail['is_available'],
                    'is_booked' => false,
                    'is_lunch' => false,
                ];

                $bookedSlot = collect($bookings)->firstWhere('time', $avail['time']);

                if ($bookedSlot && $bookedSlot['is_booked']) {
                    $slot['is_booked'] = true;
                    $slot['reception_id'] = $bookedSlot['reception_id'];
                }

                $result[] = $slot;
            }
        }

        return response()->json($result);
    }
}
