<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CaptureEmployeeToken
{
    public function handle(Request $request, Closure $next)
    {
        // If accessing login page with a token parameter, store it in session
        if ($request->route()->named('login') && $request->has('token')) {
            session(['employee_login_token' => $request->input('token')]);

            Log::info('QR login token captured on login route', [
                'ip' => $request->ip(),
                'agent' => $request->userAgent(),
                'token_preview' => Str::limit($request->input('token'), 12, '...'),
            ]);
        }

        return $next($request);
    }
}
