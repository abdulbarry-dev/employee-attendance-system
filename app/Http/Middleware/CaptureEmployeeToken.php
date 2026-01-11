<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CaptureEmployeeToken
{
    public function handle(Request $request, Closure $next)
    {
        // If accessing login page with a token parameter, store it in session
        if ($request->route()->named('login') && $request->has('token')) {
            session(['employee_login_token' => $request->input('token')]);
        }

        return $next($request);
    }
}
