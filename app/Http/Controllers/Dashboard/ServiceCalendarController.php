<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Http\Request;

class ServiceCalendarController extends Controller
{
    public function index(ServiceType $type, Patient $patient, Request $request)
    {
        $query = Service::query();
        $query->where('service_type_id', $type->id)->where('is_active', true);

        if ($type->id == 4) {
            $query->where('is_appointment', true);
        }

        $services = $query->get();

        return view('admin.service_calendar.appointment', compact('services', 'patient'));
    }
}
