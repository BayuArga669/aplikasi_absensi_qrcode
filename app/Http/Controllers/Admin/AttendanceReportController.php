<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AttendanceService;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class AttendanceReportController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index(Request $request)
    {
        // Get filters from request
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $employeeId = $request->employee_id;
        $status = $request->status;
        $department = $request->department;

        // Build query for attendance records
        $query = \App\Models\Attendance::with('user');

        if ($startDate) {
            $query->whereDate('check_in_time', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('check_in_time', '<=', $endDate);
        }

        if ($employeeId) {
            $query->where('user_id', $employeeId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($department) {
            $query->whereHas('user', function ($q) use ($department) {
                $q->where('department', 'like', '%' . $department . '%');
            });
        }

        $attendanceRecords = $query->orderBy('check_in_time', 'desc')->paginate(20);

        // Apply the same filters for statistics
        $statsQuery = \App\Models\Attendance::with('user');

        if ($startDate) {
            $statsQuery->whereDate('check_in_time', '>=', $startDate);
        }

        if ($endDate) {
            $statsQuery->whereDate('check_in_time', '<=', $endDate);
        }

        if ($employeeId) {
            $statsQuery->where('user_id', $employeeId);
        }

        if ($department) {
            $statsQuery->whereHas('user', function ($q) use ($department) {
                $q->where('department', 'like', '%' . $department . '%');
            });
        }

        // Calculate individual counts only if no specific status filter is applied
        $attendanceQuery = \App\Models\Attendance::with('user');

        if ($startDate) {
            $attendanceQuery->whereDate('check_in_time', '>=', $startDate);
        }

        if ($endDate) {
            $attendanceQuery->whereDate('check_in_time', '<=', $endDate);
        }

        if ($employeeId) {
            $attendanceQuery->where('user_id', $employeeId);
        }

        if ($department) {
            $attendanceQuery->whereHas('user', function ($q) use ($department) {
                $q->where('department', 'like', '%' . $department . '%');
            });
        }

        if ($status) {
            // If a specific status is selected in filters, use the attendance query for that count
            if ($status === 'on_time') {
                $presentCount = $attendanceQuery->clone()->where('status', 'on_time')->count();
                $lateCount = $attendanceQuery->clone()->where('status', 'late')->count();
                $absentFromRecords = $attendanceQuery->clone()->where('status', 'absent')->count();
                $realTimeAbsentCount = $this->calculateAbsentCount($startDate, $endDate, $employeeId, $department);
                $absentCount = $absentFromRecords + $realTimeAbsentCount;
                // For specific status, show only that count as active, others zeroed for display
                // But total still represents all possible attendance events
            } elseif ($status === 'late') {
                $presentCount = $attendanceQuery->clone()->where('status', 'on_time')->count();
                $lateCount = $attendanceQuery->clone()->where('status', 'late')->count();
                $absentFromRecords = $attendanceQuery->clone()->where('status', 'absent')->count();
                $realTimeAbsentCount = $this->calculateAbsentCount($startDate, $endDate, $employeeId, $department);
                $absentCount = $absentFromRecords + $realTimeAbsentCount;
            } elseif ($status === 'absent') {
                $presentCount = $attendanceQuery->clone()->where('status', 'on_time')->count();
                $lateCount = $attendanceQuery->clone()->where('status', 'late')->count();
                $absentFromRecords = $attendanceQuery->clone()->where('status', 'absent')->count();
                $realTimeAbsentCount = $this->calculateAbsentCount($startDate, $endDate, $employeeId, $department);
                $absentCount = $absentFromRecords + $realTimeAbsentCount;
            } else {
                $presentCount = $attendanceQuery->clone()->where('status', 'on_time')->count();
                $lateCount = $attendanceQuery->clone()->where('status', 'late')->count();
                $absentFromRecords = $attendanceQuery->clone()->where('status', 'absent')->count();
                $realTimeAbsentCount = $this->calculateAbsentCount($startDate, $endDate, $employeeId, $department);
                $absentCount = $absentFromRecords + $realTimeAbsentCount;
            }
        } else {
            // No specific status filter, calculate all counts
            $presentCount = $attendanceQuery->clone()->where('status', 'on_time')->count();
            $lateCount = $attendanceQuery->clone()->where('status', 'late')->count();
            $absentFromRecords = $attendanceQuery->clone()->where('status', 'absent')->count();

            // For real-time absent count (employees who should have checked in but didn't)
            // This includes both explicitly marked absent records and employees who didn't check in at all
            $realTimeAbsentCount = $this->calculateAbsentCount($startDate, $endDate, $employeeId, $department);
            $absentCount = $absentFromRecords + $realTimeAbsentCount;
        }

        // Total attendance is the sum of all three categories
        $totalAttendance = $presentCount + $lateCount + $absentCount;

        // Get all employees for filter dropdown
        $employees = \App\Models\User::where('role', 'employee')->get();

        return view('admin.reports.index', compact(
            'attendanceRecords',
            'totalAttendance',
            'presentCount',
            'lateCount',
            'absentCount',
            'employees'
        ));
    }

    /**
     * Calculate real-time absent count for employees who didn't check in
     */
    private function calculateAbsentCount($startDate = null, $endDate = null, $employeeId = null, $department = null)
    {
        if (!$startDate && !$endDate) {
            return 0;
        }

        // Get all active employees based on filters
        $employeeQuery = \App\Models\User::where('role', 'employee')->where('is_active', true);

        if ($employeeId) {
            $employeeQuery->where('id', $employeeId);
        }

        if ($department) {
            $employeeQuery->where('department', 'like', '%' . $department . '%');
        }

        $allEmployees = $employeeQuery->get();

        if ($allEmployees->isEmpty()) {
            return 0;
        }

        $start = $startDate ? Carbon::parse($startDate) : Carbon::today();
        $end = $endDate ? Carbon::parse($endDate) : Carbon::today();

        // If date range is for a single day
        if ($start->equalTo($end)) {
            // Get employees who had attendance on this date
            $attendedEmployeeIds = \App\Models\Attendance::whereIn('user_id', $allEmployees->pluck('id'))
                ->whereDate('check_in_time', $start)
                ->pluck('user_id')
                ->toArray();

            // Get employees who were on approved leave on this date
            $onLeaveEmployeeIds = \App\Models\LeaveRequest::whereIn('user_id', $allEmployees->pluck('id'))
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $start)
                ->whereDate('end_date', '>=', $start)
                ->pluck('user_id')
                ->toArray();

            // Count employees who were scheduled to work, not on leave, and didn't attend
            $absentCount = 0;
            foreach ($allEmployees as $employee) {
                $isScheduled = $this->isWorkDay($employee, $start);

                if ($isScheduled &&
                    !in_array($employee->id, $attendedEmployeeIds) &&
                    !in_array($employee->id, $onLeaveEmployeeIds)) {
                    $absentCount++;
                }
            }

            return $absentCount;
        }

        // For date ranges, calculate for each day in the range
        $totalAbsentCount = 0;
        $currentDate = clone $start;

        while ($currentDate->lte($end)) {
            // Get employees who had attendance on this date
            $attendedEmployeeIds = \App\Models\Attendance::whereIn('user_id', $allEmployees->pluck('id'))
                ->whereDate('check_in_time', $currentDate)
                ->pluck('user_id')
                ->toArray();

            // Get employees who were on approved leave on this date
            $onLeaveEmployeeIds = \App\Models\LeaveRequest::whereIn('user_id', $allEmployees->pluck('id'))
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $currentDate)
                ->whereDate('end_date', '>=', $currentDate)
                ->pluck('user_id')
                ->toArray();

            // Count employees who were scheduled to work, not on leave, and didn't attend
            foreach ($allEmployees as $employee) {
                $isScheduled = $this->isWorkDay($employee, $currentDate);

                if ($isScheduled &&
                    !in_array($employee->id, $attendedEmployeeIds) &&
                    !in_array($employee->id, $onLeaveEmployeeIds)) {
                    $totalAbsentCount++;
                }
            }

            $currentDate->addDay();
        }

        return $totalAbsentCount;
    }

    /**
     * Calculate scheduled attendance count (total potential attendance based on schedules)
     */
    private function calculateScheduledAttendanceCount($startDate = null, $endDate = null, $employeeId = null, $department = null)
    {
        if (!$startDate && !$endDate) {
            return 0;
        }

        // Get all active employees based on filters
        $employeeQuery = \App\Models\User::where('role', 'employee')->where('is_active', true);

        if ($employeeId) {
            $employeeQuery->where('id', $employeeId);
        }

        if ($department) {
            $employeeQuery->where('department', 'like', '%' . $department . '%');
        }

        $allEmployees = $employeeQuery->get();

        if ($allEmployees->isEmpty()) {
            return 0;
        }

        $start = $startDate ? Carbon::parse($startDate) : Carbon::today();
        $end = $endDate ? Carbon::parse($endDate) : Carbon::today();

        // Calculate total scheduled attendance across all days
        $totalScheduledCount = 0;
        $currentDate = clone $start;

        while ($currentDate->lte($end)) {
            foreach ($allEmployees as $employee) {
                $isScheduled = $this->isWorkDay($employee, $currentDate);

                if ($isScheduled) {
                    $totalScheduledCount++;
                }
            }

            $currentDate->addDay();
        }

        return $totalScheduledCount;
    }

    /**
     * Check if a date is a working day for an employee
     */
    private function isWorkDay($employee, $date)
    {
        $userSchedule = $employee->userSchedules()
            ->where('effective_date', '<=', $date)
            ->orderBy('effective_date', 'desc')
            ->first();

        if ($userSchedule) {
            $workSchedule = $userSchedule->workSchedule;
        } else {
            $workSchedule = \App\Models\WorkSchedule::where('is_default', true)->first();
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

    public function daily(Request $request)
    {
        return $this->index($request);
    }

    public function weekly(Request $request)
    {
        return $this->index($request);
    }

    public function monthly(Request $request)
    {
        return $this->index($request);
    }

    public function export(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $employeeId = $request->employee_id;
        $status = $request->status;
        $department = $request->department;

        // Prepare filters for export
        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'employee_id' => $employeeId,
            'status' => $status,
            'department' => $department,
        ];

        // Create export instance with filters
        $export = new \App\Exports\AttendanceReportExport($filters);

        // Generate filename with timestamp
        $filename = 'attendance_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download($export, $filename);
    }

    public function exportDaily(Request $request)
    {
        return $this->export($request);
    }

    public function exportWeekly(Request $request)
    {
        return $this->export($request);
    }

    public function exportMonthly(Request $request)
    {
        return $this->export($request);
    }
}