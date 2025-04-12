<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExaminationMapIsActive
{
    /**
     * @param Request $request
     * @param Closure(Request): (Response) $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->route()->action['as'] == 'maps.show' || is_null($request->map)) {
            return $next($request);
        }

        return !$request->map->is_active ? redirect()->back()->withErrors(['Карта не активна!']) : $next($request);
    }
}
