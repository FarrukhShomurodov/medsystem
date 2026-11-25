<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ends the session of an account that stopped being active while signed in (its employee was
 * dismissed, see Admin::isActive()): sign-in refuses such accounts, and this catches the sessions
 * and "remember me" cookies they already hold on their next request.
 */
final class EnsureAccountIsActive
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $account = $request->user(Admin::GUARD);

        if (! $account instanceof Admin || $account->isActive()) {
            return $next($request);
        }

        Auth::guard(Admin::GUARD)->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = __('auth.inactive');

        return $request->is('api/*') || $request->expectsJson()
            ? response()->json(['message' => $message], Response::HTTP_UNAUTHORIZED)
            : to_route('dashboard.login')->withErrors(['login' => $message]);
    }
}
