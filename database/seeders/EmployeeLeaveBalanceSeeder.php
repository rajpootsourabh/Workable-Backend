<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\TimeOffType;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeLeaveBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year = Carbon::now()->year;

        $employees = Employee::all();
        $leaveTypes = TimeOffType::all();

        // Customize allocation based on leave type name
        $defaultAllocations = [
            'Paid Time Off' => 12,
            'Sick Leave' => 6,
            'Unpaid Leave' => 0,
        ];

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $type) {
                $allocatedDays = $defaultAllocations[$type->name] ?? 0;

                EmployeeLeaveBalance::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'time_off_type_id' => $type->id,
                        'year' => $year,
                    ],
                    [
                        'allocated_days' => $allocatedDays,
                        'used_days' => 0,
                        'carried_forward' => 0,
                    ]
                );
            }
        }
    }
}
