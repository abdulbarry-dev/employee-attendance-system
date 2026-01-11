<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureFreshEmployeeQrSession
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Only enforce for authenticated non-admin users
        if ($user && !$user->hasRole('admin')) {
            $cachedQr = cache()->get('office_kiosk_qr');
            $currentVersion = $cachedQr['version'] ?? null;
            $sessionVersion = $request->session()->get('employee_qr_version');

            if (!$currentVersion || !$sessionVersion || $currentVersion !== $sessionVersion) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Your QR session expired. Please scan the latest QR code to log in again.',
                ]);
            }
        }

        return $next($request);
    }
}
