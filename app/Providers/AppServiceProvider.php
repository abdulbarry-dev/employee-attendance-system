<?php

namespace App\Providers;

use App\Models\User;
use App\Notifications\FailedJobAlert;
use App\Policies\EmployeePolicy;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Horizon\Horizon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            LoginResponse::class,
            \App\Http\Responses\LoginResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, EmployeePolicy::class);

        if (config('app.url')) {
            URL::forceRootUrl(config('app.url'));
        }

        // Horizon Authorization: Only super-admin can access
        Horizon::auth(function ($request) {
            return $request->user()?->hasRole('super-admin') ?? false;
        });

        // Listen for failed jobs and notify admins
        Queue::failing(function (JobFailed $event) {
            $this->notifyAdminsOfFailedJob($event);
        });
    }

    /**
     * Notify all super-admin users of a failed job.
     */
    protected function notifyAdminsOfFailedJob(JobFailed $event): void
    {
        try {
            $admins = User::role('super-admin')->get();

            foreach ($admins as $admin) {
                $admin->notify(new FailedJobAlert(
                    jobName: $event->job->resolveName(),
                    exception: $event->exception->getMessage(),
                    failedAt: now()->toDateTimeString()
                ));
            }
        } catch (\Exception $e) {
            // Log silently if role doesn't exist or notification fails
            logger()->error('Failed to notify admins of job failure: '.$e->getMessage());
        }
    }
}
