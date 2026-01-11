<?php

namespace App\Livewire\Employee;

use App\Models\User;
use App\Notifications\EmployeeWelcome;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class EmployeeForm extends Component
{
    use AuthorizesRequests;

    public ?User $employee = null;

    #[\Livewire\Attributes\Validate]
    public string $first_name = '';

    #[\Livewire\Attributes\Validate]
    public string $last_name = '';

    #[\Livewire\Attributes\Validate]
    public string $email = '';

    #[\Livewire\Attributes\Validate]
    public string $phone_number = '';

    public ?string $monthly_salary = null;
    public ?string $shift_start = null;
    public ?string $shift_end = null;
    public int $grace_period_minutes = 10;
    public int $break_allowance_minutes = 60;
    public array $working_days = ['mon', 'tue', 'wed', 'thu', 'fri'];

    public function mount(?User $employee = null)
    {
        $this->authorize('create', User::class);

        if ($employee) {
            $this->employee = $employee;
            $this->first_name = $employee->first_name;
            $this->last_name = $employee->last_name;
            $this->email = $employee->email;
            $this->phone_number = $employee->phone_number;
            $this->monthly_salary = $employee->monthly_salary;
            $this->shift_start = $employee->shift_start?->format('H:i');
            $this->shift_end = $employee->shift_end?->format('H:i');
            $this->grace_period_minutes = (int) ($employee->grace_period_minutes ?? 10);
            $this->break_allowance_minutes = (int) ($employee->break_allowance_minutes ?? 60);
            $this->working_days = $employee->working_days ?? ['mon', 'tue', 'wed', 'thu', 'fri'];
        }
    }


    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email' . ($this->employee ? ',' . $this->employee->id : ''),
            'phone_number' => 'required|string|regex:/^(\+?[0-9]{1,3}[-.\s]?)?[0-9]{1,14}$/',
            'monthly_salary' => 'nullable|numeric|min:0|max:999999.99',
            'shift_start' => 'nullable|date_format:H:i',
            'shift_end' => 'nullable|date_format:H:i',
            'grace_period_minutes' => 'required|integer|min:0|max:120',
            'break_allowance_minutes' => 'required|integer|min:0|max:480',
            'working_days' => 'required|array',
            'working_days.*' => 'in:sun,mon,tue,wed,thu,fri,sat',
        ];
    }
    public function submit()
    {
        $validated = $this->validate($this->rules());

        $validated['name'] = trim($this->first_name . ' ' . $this->last_name);

        if ($this->employee) {
            $this->authorize('update', $this->employee);
            $this->employee->update($validated);
        } else {
            $this->authorize('create', User::class);

            // Create employee with temporary password
            $tempPassword = str()->random(12);
            $employee = User::create([
                ...$validated,
                'name' => $validated['name'],
                'password' => bcrypt($tempPassword),
            ]);

            // Assign employee role
            $employee->assignRole('employee');

            // Send welcome email
            $employee->notify(new EmployeeWelcome($employee, $tempPassword));
        }

        $this->redirect(route('employees.index'));
    }

    public function render()
    {
        return view('livewire.employee.employee-form');
    }
}
