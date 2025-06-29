<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\TimeOffType;

class EmployeeObserver
{
    /**
     * Handle the Employee "created" event.
     */
    public function created(Employee $employee): void
    {
        $currentYear = now()->year;

        // Leave types must match what's seeded
        $defaultLeaves = [
            ['type' => 'Sick Leave', 'days' => 10],
            ['type' => 'Paid Time Off', 'days' => 15],
            ['type' => 'Unpaid Leave', 'days' => 0], // Generally no allocation
        ];

        foreach ($defaultLeaves as $leave) {
            $timeOffType = TimeOffType::where('name', $leave['type'])->first();

            if ($timeOffType) {
                EmployeeLeaveBalance::create([
                    'employee_id' => $employee->id,
                    'time_off_type_id' => $timeOffType->id,
                    'year' => $currentYear,
                    'allocated_days' => $leave['days'],
                    'used_days' => 0,
                    'carried_forward' => 0,
                ]);
            }
        }
    }

    /**
     * Handle the Employee "updated" event.
     */
    public function updated(Employee $employee): void
    {
        //
    }

    /**
     * Handle the Employee "deleted" event.
     */
    public function deleted(Employee $employee): void
    {
        //
    }

    /**
     * Handle the Employee "restored" event.
     */
    public function restored(Employee $employee): void
    {
        //
    }

    /**
     * Handle the Employee "force deleted" event.
     */
    public function forceDeleted(Employee $employee): void
    {
        //
    }
}
