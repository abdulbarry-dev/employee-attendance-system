<?php

namespace App\Livewire\Employee;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeList extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public bool $showDeleteModal = false;

    public ?int $employeeIdToDelete = null;

    public bool $showBanModal = false;

    public ?int $employeeIdToBan = null;

    public string $banReason = '';

    public function employees()
    {
        $this->authorize('viewAny', User::class);

        return User::role('employee')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('phone_number', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                if ($this->statusFilter === 'active') {
                    $query->where('is_banned', false);
                } elseif ($this->statusFilter === 'banned') {
                    $query->where('is_banned', true);
                }
            })
            ->latest()
            ->paginate(5);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function confirmBan(User $employee)
    {
        $this->authorize('ban', $employee);

        $this->employeeIdToBan = $employee->id;
        $this->banReason = '';
        $this->showBanModal = true;
    }

    public function ban()
    {
        if (! $this->employeeIdToBan) {
            return;
        }

        $employee = User::find($this->employeeIdToBan);

        if ($employee) {
            $this->authorize('ban', $employee);
            $employee->update([
                'is_banned' => true,
                'ban_reason' => $this->banReason ?: null,
                'banned_at' => now(),
            ]);
        }

        $this->closeBanModal();
    }

    public function closeBanModal()
    {
        $this->showBanModal = false;
        $this->employeeIdToBan = null;
        $this->banReason = '';
    }

    public function unban(User $employee)
    {
        $this->authorize('unban', $employee);

        $employee->update([
            'is_banned' => false,
            'ban_reason' => null,
            'banned_at' => null,
        ]);
    }

    public function confirmDelete(int $employeeId)
    {
        $this->employeeIdToDelete = $employeeId;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $employee = User::find($this->employeeIdToDelete);

        if ($employee) {
            $this->authorize('delete', $employee);
            $employee->delete();
        }

        $this->showDeleteModal = false;
        $this->employeeIdToDelete = null;
    }

    public function render()
    {
        return view('livewire.employee.employee-list', [
            'employees' => $this->employees(),
        ]);
    }
}
