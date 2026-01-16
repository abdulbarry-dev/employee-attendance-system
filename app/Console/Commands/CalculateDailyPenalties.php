<?php

namespace App\Console\Commands;

use App\Jobs\CalculatePenalties;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculateDailyPenalties extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'attendance:calculate-penalties
                            {--date= : Process penalties for a specific date (Y-m-d)}
                            {--force : Force recalculation even if penalties exist}';

    /**
     * The console command description.
     */
    protected $description = 'Calculate and apply penalties for incomplete or late attendances';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::yesterday();

        $this->info("Calculating penalties for {$date->toDateString()}...");

        // Get all attendances for the date that might need penalty calculation
        $attendances = Attendance::whereDate('date', $date)
            ->whereNotNull('check_in')
            ->with(['user', 'shift', 'breaks', 'penalties'])
            ->get();

        if ($attendances->isEmpty()) {
            $this->info('No attendances found for this date.');

            return self::SUCCESS;
        }

        $processedCount = 0;
        $skippedCount = 0;

        foreach ($attendances as $attendance) {
            // Check if late penalty already exists
            $hasLatePenalty = $attendance->penalties()
                ->where('type', 'late')
                ->exists();

            // Dispatch late penalty calculation if not already processed or forcing
            if (! $hasLatePenalty || $this->option('force')) {
                CalculatePenalties::dispatch($attendance, 'late');
                $processedCount++;
            } else {
                $skippedCount++;
            }

            // Check break penalties if breaks exist
            if ($attendance->breaks->isNotEmpty()) {
                $hasBreakPenalty = $attendance->penalties()
                    ->where('type', 'break_overage')
                    ->exists();

                if (! $hasBreakPenalty || $this->option('force')) {
                    $totalBreakMinutes = $attendance->total_break_duration;
                    CalculatePenalties::dispatch($attendance, 'break_overage', $totalBreakMinutes);
                    $processedCount++;
                }
            }
        }

        $this->info("Processed: {$processedCount} penalty calculations queued");
        $this->info("Skipped: {$skippedCount} (already calculated)");

        return self::SUCCESS;
    }
}
