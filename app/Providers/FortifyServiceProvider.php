<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Models\EmployeeLoginToken;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Events\Login as FortifyLogin;
use Laravel\Fortify\Fortify;

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

            // Check if user is banned - block login before proceeding
            if ($user->isBanned()) {
                throw ValidationException::withMessages([
                    Fortify::username() => [$this->getBannedMessage($user)],
                ]);
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

            // Store the validated token temporarily for post-login handling
            request()->attributes->set('validated_employee_login_token', $tokenString);
            session()->forget('employee_login_token');

            // Store QR version in session immediately during authentication
            $cachedQr = cache()->get('office_kiosk_qr');
            $version = $cachedQr['version'] ?? null;

            if ($version) {
                $request->session()->put('employee_qr_version', $version);
            }

            return $user;
        });

        // After successful login, tie the session to the current QR version and mark token used
        Event::listen(FortifyLogin::class, function (FortifyLogin $event) {
            $user = $event->user;

            // Admins are exempt from QR enforcement
            if ($user->hasRole('admin')) {
                return;
            }

            $tokenString = request()->attributes->get('validated_employee_login_token');

            if ($tokenString) {
                $token = EmployeeLoginToken::where('token', $tokenString)->first();

                if ($token && $token->isValid()) {
                    $token->update([
                        'used_at' => now(),
                        'user_id' => $user->id,
                    ]);
                }
            }

            $cachedQr = cache()->get('office_kiosk_qr');
            $version = $cachedQr['version'] ?? null;

            if ($version) {
                session()->put('employee_qr_version', $version);
                session()->save(); // Force session save immediately
                request()->session()->put('employee_qr_version', $version);
            }
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

    /**
     * Get a user-friendly message for banned users.
     */
    private function getBannedMessage(User $user): string
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
