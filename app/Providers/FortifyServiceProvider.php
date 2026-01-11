<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\EmployeeLoginToken;


class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);

        // Use custom authentication to validate employee login tokens
        Fortify::authenticateUsing(function ($request) {
            $credentials = $request->only(Fortify::username(), 'password');

            // Find user by email
            $user = User::where(Fortify::username(), $credentials[Fortify::username()])->first();

            // Validate credentials
            if (! $user || ! Hash::check($credentials['password'], $user->password)) {
                return false;
            }

            // Check if user is admin - admins can log in without QR token
            if ($user->hasRole('admin')) {
                return $user;
            }

            // For employees: require QR token
            $tokenString = $request->input('token') ?? session('employee_login_token');

            if (! $tokenString) {
                throw ValidationException::withMessages([
                    Fortify::username() => ['Please scan the QR code at the office kiosk to log in.'],
                ]);
            }

            // Validate token
            $token = EmployeeLoginToken::where('token', $tokenString)->first();

            if (! $token) {
                session()->forget('employee_login_token');
                throw ValidationException::withMessages([
                    Fortify::username() => ['Invalid login token. Please scan the QR code again.'],
                ]);
            }

            if (! $token->isValid()) {
                session()->forget('employee_login_token');
                throw ValidationException::withMessages([
                    Fortify::username() => ['Your login token has expired. Please scan the QR code again.'],
                ]);
            }

            // DON'T mark token as used here - Fortify calls this multiple times
            // Token will be marked as used after successful login
            // $token->update(['used_at' => now(), 'user_id' => $user->id]);
            session()->forget('employee_login_token');

            return $user;
        });
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn () => view('livewire.auth.login'));
        Fortify::verifyEmailView(fn () => view('livewire.auth.verify-email'));
        Fortify::twoFactorChallengeView(fn () => view('livewire.auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn () => view('livewire.auth.confirm-password'));
        Fortify::registerView(fn () => view('livewire.auth.register'));
        Fortify::resetPasswordView(fn () => view('livewire.auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn () => view('livewire.auth.forgot-password'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
