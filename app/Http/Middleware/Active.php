<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class Active
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->isActive()) {
            return $next($request);
        }

        if (Auth::check()) {
            // /home sirf auth use karta hai aur wapas /dashboard par bhejta hai — inactive user par redirect loop hota tha
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', __('db.Sorry! You are not allowed to access this module'));
        }

        return redirect()->route('login');
    }
}
