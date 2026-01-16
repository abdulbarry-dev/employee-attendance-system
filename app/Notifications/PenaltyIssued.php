<?php

namespace App\Notifications;

use App\Models\EmployeePenalty;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PenaltyIssued extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public EmployeePenalty $penalty) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Penalty notice'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->first_name ?? $notifiable->name]))
            ->line(__('A penalty has been recorded on :date.', [
                'date' => $this->penalty->occurred_on->toFormattedDateString(),
            ]))
            ->line(__('Type: :type', ['type' => ucfirst(str_replace('_', ' ', $this->penalty->type))]))
            ->line(__('Amount: :amount', ['amount' => number_format($this->penalty->penalty_amount, 2)]))
            ->when($this->penalty->minutes_late > 0, function (MailMessage $message) {
                return $message->line(__('Minutes late: :minutes', ['minutes' => $this->penalty->minutes_late]));
            })
            ->when($this->penalty->break_overage_minutes > 0, function (MailMessage $message) {
                return $message->line(__('Break overage (minutes): :minutes', ['minutes' => $this->penalty->break_overage_minutes]));
            })
            ->line(__('If you have questions, please contact your manager.'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'penalty_id' => $this->penalty->id,
            'type' => $this->penalty->type,
            'amount' => $this->penalty->penalty_amount,
            'minutes_late' => $this->penalty->minutes_late,
            'break_overage_minutes' => $this->penalty->break_overage_minutes,
            'occurred_on' => $this->penalty->occurred_on,
        ];
    }
}
