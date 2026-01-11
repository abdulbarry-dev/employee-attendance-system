<?php

namespace App\Actions\Fortify;

use App\Models\EmployeeLoginToken;
use App\Models\User;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class AttemptToAuthenticate
{
    protected $guard;

    public function __construct(StatefulGuard $guard)
    {
        $this->guard = $guard;
    }

    public function handle($request, $next)
    {
        // Get credentials
        $credentials = $request->only(Fortify::username(), 'password');

        // Find user by email
        $user = User::where(Fortify::username(), $credentials[Fortify::username()])->first();

        // Check if user exists and password is correct
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                Fortify::username() => [trans('auth.failed')],
            ]);
        }

        // If user is NOT an admin, require token
        if (! $user->hasRole('admin')) {
            $tokenString = session('employee_login_token');

            if (! $tokenString) {
                throw ValidationException::withMessages([
                    Fortify::username() => ['Please scan the QR code at the office kiosk to log in.'],
                ]);
            }

            $token = EmployeeLoginToken::where('token', $tokenString)->first();

            if (! $token || ! $token->isValid()) {
                session()->forget('employee_login_token');
                throw ValidationException::withMessages([
                    Fortify::username() => ['Your login token has expired. Please scan the QR code again.'],
                ]);
            }

            // Mark token as used and associate with user
            $token->update([
                'used_at' => now(),
                'user_id' => $user->id,
            ]);

            // Clear token from session
            session()->forget('employee_login_token');
        }

        // Attempt login
        $this->guard->login($user, $request->filled('remember'));

        return $next($request);
    }
}
