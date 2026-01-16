<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Services\AttendancePenaltyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculatePenalties implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Attendance $attendance,
        public string $penaltyType,
        public ?int $breakMinutes = null
    ) {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(AttendancePenaltyService $penaltyService): void
    {
        if ($this->penaltyType === 'late') {
            $penaltyService->applyLatePenalty($this->attendance);
        } elseif ($this->penaltyType === 'break_overage' && $this->breakMinutes !== null) {
            $penaltyService->applyBreakOveragePenalty($this->attendance, $this->breakMinutes);
        }
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [60, 300, 1500]; // 1 min, 5 min, 25 min
    }
}
