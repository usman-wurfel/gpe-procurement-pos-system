<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = Auth::user();

        if (!$user) {
           return redirect('/')->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        if (! in_array($permission, role_permission_names((int) $user->role_id), true)) {
            return redirect('/')->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        return $next($request);
    }
}
