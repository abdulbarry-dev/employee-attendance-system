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
use Illuminate\Support\Facades\Event;
use Laravel\Fortify\Events\Login as FortifyLogin;
use Illuminate\Support\Facades\Log;
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

            // Check if user is banned - block login before proceeding
            if ($user->isBanned()) {
                Log::warning('Banned user attempted login', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip(),
                    'ban_reason' => $user->ban_reason,
                    'banned_at' => $user->banned_at,
                ]);
                throw ValidationException::withMessages([
                    Fortify::username() => [$this->getBannedMessage($user)],
                ]);
            }

            // Check if user is admin - admins can log in without QR token
            if ($user->hasRole('admin')) {
                Log::info('Admin login bypasses QR token', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip(),
                ]);
                return $user;
            }

            // For employees: require QR token
            $tokenString = $request->input('token') ?? session('employee_login_token');

            if (! $tokenString) {
                Log::warning('QR login missing token', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip(),
                ]);
                throw ValidationException::withMessages([
                    Fortify::username() => ['Please scan the QR code at the office kiosk to log in.'],
                ]);
            }

            // Validate token
            $token = EmployeeLoginToken::where('token', $tokenString)->first();

            if (! $token) {
                Log::warning('QR login invalid token', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip(),
                    'token_preview' => Str::limit($tokenString, 12, '...'),
                ]);
                session()->forget('employee_login_token');
                throw ValidationException::withMessages([
                    Fortify::username() => ['Invalid login token. Please scan the QR code again.'],
                ]);
            }

            if (! $token->isValid()) {
                Log::warning('QR login expired or used token', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip(),
                    'token_preview' => Str::limit($tokenString, 12, '...'),
                    'expires_at' => optional($token->expires_at)->toDateTimeString(),
                    'used_at' => optional($token->used_at)->toDateTimeString(),
                ]);
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

                Log::info('QR login token validated and version stored', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip(),
                    'token_preview' => Str::limit($tokenString, 12, '...'),
                    'version' => $version,
                ]);
            } else {
                Log::warning('QR login token validated but no QR version in cache', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip(),
                    'cached_qr_exists' => (bool) $cachedQr,
                ]);
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

                    Log::info('QR login token marked used after login', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'token_preview' => Str::limit($tokenString, 12, '...'),
                    ]);
                }
            }

            $cachedQr = cache()->get('office_kiosk_qr');
            $version = $cachedQr['version'] ?? null;

            if ($version) {
                session()->put('employee_qr_version', $version);
                session()->save(); // Force session save immediately
                request()->session()->put('employee_qr_version', $version);

                Log::info('QR session version stored after login', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'version' => $version,
                    'session_check' => session()->get('employee_qr_version'),
                ]);
            } else {
                Log::warning('No QR version available in cache after login', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'cached_qr_exists' => (bool) $cachedQr,
                ]);
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
            return "Your account has been suspended. Reason: {$banReason}" . ($bannedAt ? " (Suspended on {$bannedAt})" : "");
        }

        return $bannedAt 
            ? "Your account has been suspended on {$bannedAt}. Please contact the administrator for assistance."
            : "Your account has been suspended. Please contact the administrator for assistance.";
    }
}
