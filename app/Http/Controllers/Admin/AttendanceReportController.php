<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AttendanceService;

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
        
        $attendanceRecords = $query->orderBy('check_in_time', 'desc')->paginate(20);
        
        // Apply the same filters for statistics
        $statsQuery = \App\Models\Attendance::with('employee');
        
        if ($startDate) {
            $statsQuery->whereDate('check_in_time', '>=', $startDate);
        }
        
        if ($endDate) {
            $statsQuery->whereDate('check_in_time', '<=', $endDate);
        }
        
        if ($employeeId) {
            $statsQuery->where('user_id', $employeeId);
        }
        
        $totalAttendance = $statsQuery->count();
        $presentCount = $statsQuery->where('status', 'present')->count();
        $lateCount = $statsQuery->where('status', 'late')->count();
        $absentCount = $totalAttendance - ($presentCount + $lateCount);
        
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
        // Handle report export logic
        return response()->json(['message' => 'Export functionality would be implemented here']);
    }
}