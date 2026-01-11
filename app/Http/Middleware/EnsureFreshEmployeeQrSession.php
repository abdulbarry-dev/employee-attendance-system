<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

            Log::info('QR session check middleware', [
                'user_id' => $user->id,
                'email' => $user->email,
                'route' => $request->route()?->getName(),
                'current_version' => $currentVersion,
                'session_version' => $sessionVersion,
                'cached_qr_exists' => (bool) $cachedQr,
            ]);

            if (!$currentVersion || !$sessionVersion || $currentVersion !== $sessionVersion) {
                Log::warning('QR session version mismatch, logging out employee', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'current_version' => $currentVersion,
                    'session_version' => $sessionVersion,
                    'cached_qr_exists' => (bool) $cachedQr,
                ]);

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
