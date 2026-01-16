<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FailedJobAlert extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $jobName,
        public string $exception,
        public string $failedAt
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'job_name' => $this->jobName,
            'exception' => $this->exception,
            'failed_at' => $this->failedAt,
            'type' => 'failed_job',
        ];
    }

    /**
     * Get the notification's database representation.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Background Job Failed',
            'message' => "Job '{$this->jobName}' failed: {$this->exception}",
            'job_name' => $this->jobName,
            'exception' => $this->exception,
            'failed_at' => $this->failedAt,
            'type' => 'failed_job',
            'action_url' => '/horizon/failed',
        ];
    }
}
