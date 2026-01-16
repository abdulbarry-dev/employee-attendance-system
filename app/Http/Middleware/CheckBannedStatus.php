<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBannedStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and banned
        if ($request->user() && $request->user()->isBanned()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')
                ->withErrors(['email' => $this->getBannedMessage($request->user())]);
        }

        return $next($request);
    }

    /**
     * Get a user-friendly message for banned users.
     */
    private function getBannedMessage($user): string
    {
        $banReason = $user->ban_reason ? trim($user->ban_reason) : null;
        $bannedAt = $user->banned_at?->format('F d, Y');

        if ($banReason) {
            return "Your account has been suspended. Reason: {$banReason}".($bannedAt ? " (Suspended on {$bannedAt})" : '');
        }

        return $bannedAt
            ? "Your account has been suspended on {$bannedAt}. Please contact the administrator for assistance."
            : 'Your account has been suspended. Please contact the administrator for assistance.';
    }
}
