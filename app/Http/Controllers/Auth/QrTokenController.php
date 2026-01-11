<?php

namespace App\Http\Controllers\Auth;

use App\Models\EmployeeLoginToken;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class QrTokenController extends Controller
{
    public function generate(Request $request)
    {
        try {
            // Generate a new login token
            $token = EmployeeLoginToken::generate(
                userId: null,
                ipAddress: $request->ip()
            );

            // Redirect to login page with token as URL parameter
            return redirect()->route('login', ['token' => $token->token])
                ->with('success', 'QR code scanned successfully. Please log in with your credentials.');

        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Failed to generate login token. Please try again.');
        }
    }
}
