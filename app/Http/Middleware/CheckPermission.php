<?php

namespace App\Http\Middleware;

use App\Models\ViewPermissions;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission_name
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission_name)
    {
        if (Auth::user()->is_an_admin()) {
            return $next($request);
        }

        if (ViewPermissions::check_permission(Auth::user()->role_id, $permission_name) == 1) {
            return $next($request);
        }

        return response(
            'Unauthorized Access. <a href="' . route('dashboard') . '">Go back to dashboard</a>',
            401
        );
    }
}
