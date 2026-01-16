<?php

namespace App\Http\Responses;

use App\Models\EmployeeLoginToken;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        // Mark the employee login token as used to prevent reuse
        $tokenString = $request->input('token') ?? session('employee_login_token');
        if ($tokenString) {
            $token = EmployeeLoginToken::where('token', $tokenString)->first();
            if ($token && ! $token->used_at) {
                $token->markAsUsed();
            }
        }

        // Redirect based on user role
        if (auth()->user()->hasRole('admin')) {
            return redirect()->intended(route('dashboard'));
        }

        // Default redirect for employees
        return redirect()->intended(route('employee.punch'));
    }
}
