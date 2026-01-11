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

    public function mount(?User $employee = null)
    {
        $this->authorize('create', User::class);

        if ($employee) {
            $this->employee = $employee;
            $this->first_name = $employee->first_name;
            $this->last_name = $employee->last_name;
            $this->email = $employee->email;
            $this->phone_number = $employee->phone_number;
        }
    }


    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email' . ($this->employee ? ',' . $this->employee->id : ''),
            'phone_number' => 'required|string|regex:/^(\+?[0-9]{1,3}[-.\s]?)?[0-9]{1,14}$/',
        ];
    }
    public function submit()
    {
        $validated = $this->validate($this->rules());

        if ($this->employee) {
            $this->authorize('update', $this->employee);
            $this->employee->update($validated);
        } else {
            $this->authorize('create', User::class);

            // Create employee with temporary password
            $tempPassword = str()->random(12);
            $employee = User::create([
                ...$validated,
                'name' => "{$this->first_name} {$this->last_name}",
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
