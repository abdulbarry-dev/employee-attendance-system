<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeWelcome extends Notification
{
    use Queueable;

    protected User $employee;

    protected string $tempPassword;

    public function __construct(User $employee, string $tempPassword)
    {
        $this->employee = $employee;
        $this->tempPassword = $tempPassword;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Set application locale for this notification
        app()->setLocale($this->employee->preferred_locale ?? config('app.locale', 'en'));

        return (new MailMessage)
            ->greeting(__("Welcome, {$this->employee->first_name}!"))
            ->line(__('Your account has been created in the Attendance System.'))
            ->line(__("Email: {$this->employee->email}"))
            ->line(__("Temporary Password: {$this->tempPassword}"))
            ->line(__('Please click the button below to set your own password:'))
            ->action(__('Set Your Password'), route('password.reset', ['token' => app('auth.password.broker')->createToken($notifiable)]))
            ->line(__('If you did not expect this email, no further action is required.'))
            ->line(__('For security reasons, please change your password immediately after logging in.'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'employee_id' => $this->employee->id,
            'welcome' => true,
        ];
    }
}
