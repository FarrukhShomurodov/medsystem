<?php

namespace App\Http\Middleware;

use App\Models\ExaminationMap;
use App\Models\Patient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExaminationMapIsActive
{
    /**
     * The patient and the map are the bound route parameters, never request input of the same name.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $patient = $request->route('patient');

        if ($request->routeIs('patient.map.create') && $patient instanceof Patient && $patient->activeExaminationMap()->exists()) {
            return back()->withErrors(['Завершить работу с активной картой пациента!']);
        }

        $map = $request->route('map');

        if ($request->routeIs('maps.show') || ! $map instanceof ExaminationMap) {
            return $next($request);
        }

        return ! $map->is_active ? back()->withErrors(['Карта не активна!']) : $next($request);
    }
}
