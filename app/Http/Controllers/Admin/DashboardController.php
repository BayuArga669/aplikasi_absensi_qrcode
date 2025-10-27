<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AttendanceService;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index()
    {
        // Get dashboard statistics
        $totalEmployees = User::count();
        $presentToday = Attendance::whereDate('check_in_time', Carbon::today())
            ->where('status', 'on_time')
            ->count();
        $lateToday = Attendance::whereDate('check_in_time', Carbon::today())
            ->where('status', 'late')
            ->count();
        $absentToday = $totalEmployees - ($presentToday + $lateToday);
        
        $recentAttendance = Attendance::with('user')
            ->whereDate('check_in_time', Carbon::today())
            ->orderBy('check_in_time', 'desc')
            ->limit(10)
            ->get();
            
        $recentLeaveRequests = LeaveRequest::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalEmployees',
            'presentToday',
            'lateToday',
            'absentToday',
            'recentAttendance',
            'recentLeaveRequests'
        ));
    }
}