<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
        return (new MailMessage)
            ->greeting("Welcome, {$this->employee->first_name}!")
            ->line('Your account has been created in the Arena Système Pointage platform.')
            ->line("Email: {$this->employee->email}")
            ->line("Temporary Password: {$this->tempPassword}")
            ->line('Please click the button below to set your own password:')
            ->action('Set Your Password', route('password.reset', ['token' => app('auth.password.broker')->createToken($notifiable)]))
            ->line('If you did not expect this email, no further action is required.')
            ->line('For security reasons, please change your password immediately after logging in.');
    }


    public function toArray(object $notifiable): array
    {
        return [
            'employee_id' => $this->employee->id,
            'welcome' => true,
        ];
    }
}

