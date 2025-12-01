<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\WorkSchedule;
use Carbon\Carbon;

class MarkDailyAbsent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mark-daily-absent {--date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark employees as absent if they did not check in for the specified date (default: yesterday)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::yesterday();

        $this->info("Processing absent marking for date: {$date->format('Y-m-d')}");

        // Get all active employees
        $employees = User::where('role', 'employee')
            ->where('is_active', true)
            ->get();

        $absentCount = 0;

        foreach ($employees as $employee) {
            // Check if employee has attendance record for the date
            $attendanceExists = Attendance::where('user_id', $employee->id)
                ->whereDate('check_in_time', $date)
                ->exists();

            // Check if employee is on leave for the date
            $onLeave = LeaveRequest::where('user_id', $employee->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->exists();

            // If no attendance record and not on leave, mark as absent
            if (!$attendanceExists && !$onLeave) {
                // Check if the date is a working day for this employee
                $isWorkDay = $this->isWorkDay($employee, $date);

                if ($isWorkDay) {
                    // Create an absent record
                    Attendance::create([
                        'user_id' => $employee->id,
                        'check_in_time' => null,
                        'check_out_time' => null,
                        'status' => 'absent',
                        'is_late' => false,
                        'late_duration' => 0,
                        'check_in_latitude' => null,
                        'check_in_longitude' => null,
                        'check_out_latitude' => null,
                        'check_out_longitude' => null,
                        'notes' => 'Marked as absent - no attendance record for the day',
                    ]);

                    $absentCount++;
                }
            }
        }

        $this->info("Successfully marked {$absentCount} employees as absent for {$date->format('Y-m-d')}");
    }

    /**
     * Check if a date is a working day for the employee
     */
    private function isWorkDay($employee, $date)
    {
        // Get employee's work schedule
        $userSchedule = $employee->userSchedules()
            ->where('effective_date', '<=', $date)
            ->orderBy('effective_date', 'desc')
            ->first();

        if ($userSchedule) {
            $workSchedule = $userSchedule->workSchedule;
        } else {
            $workSchedule = WorkSchedule::where('is_default', true)->first();
        }

        if (!$workSchedule) {
            return true; // Default to true if no schedule found
        }

        // Check if the day of week is a working day
        $dayOfWeek = $date->dayOfWeek;
        $isWorkDay = false;

        // Map Carbon's day of week to our schedule (0 = Sunday, 1 = Monday, etc.)
        switch ($dayOfWeek) {
            case 1: // Monday
                $isWorkDay = $workSchedule->monday;
                break;
            case 2: // Tuesday
                $isWorkDay = $workSchedule->tuesday;
                break;
            case 3: // Wednesday
                $isWorkDay = $workSchedule->wednesday;
                break;
            case 4: // Thursday
                $isWorkDay = $workSchedule->thursday;
                break;
            case 5: // Friday
                $isWorkDay = $workSchedule->friday;
                break;
            case 6: // Saturday
                $isWorkDay = $workSchedule->saturday;
                break;
            case 0: // Sunday
                $isWorkDay = $workSchedule->sunday;
                break;
        }

        return $isWorkDay;
    }
}
