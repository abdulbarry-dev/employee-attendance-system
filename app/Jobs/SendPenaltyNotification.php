<?php

namespace App\Jobs;

use App\Models\EmployeePenalty;
use App\Models\User;
use App\Notifications\PenaltyIssued;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPenaltyNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public EmployeePenalty $penalty
    ) {
        $this->onQueue('high');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->user->notify(new PenaltyIssued($this->penalty));
        $this->penalty->update(['notified_at' => now()]);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [60, 300, 1500]; // 1 min, 5 min, 25 min
    }
}
