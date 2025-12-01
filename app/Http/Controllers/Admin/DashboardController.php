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
        $totalEmployees = User::where('role', 'employee')->where('is_active', true)->count();
        $presentToday = Attendance::whereDate('check_in_time', Carbon::today())
            ->where('status', 'on_time')
            ->count();
        $lateToday = Attendance::whereDate('check_in_time', Carbon::today())
            ->where('status', 'late')
            ->count();

        // Calculate absent employees considering approved leave
        $onLeaveToday = LeaveRequest::where('status', 'approved')
            ->whereDate('start_date', '<=', Carbon::today())
            ->whereDate('end_date', '>=', Carbon::today())
            ->count();
        $absentToday = $totalEmployees - ($presentToday + $lateToday + $onLeaveToday);

        // Additional statistics
        $activeEmployees = User::where('role', 'employee')->where('is_active', true)->count();
        $departmentsCount = User::where('role', 'employee')
            ->where('is_active', true)
            ->distinct('department')
            ->count('department');
        $activeOffices = \App\Models\OfficeLocation::where('is_active', true)->count();

        $recentAttendance = Attendance::with('user')
            ->whereDate('check_in_time', Carbon::today())
            ->orderBy('check_in_time', 'desc')
            ->limit(10)
            ->get();

        $recentLeaveRequests = LeaveRequest::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get top late arrivals for the week
        $weekStart = Carbon::today()->startOfWeek();
        $weekEnd = Carbon::today()->endOfWeek();

        $topLateArrivals = Attendance::with('user')
            ->where('status', 'late')
            ->whereBetween('check_in_time', [$weekStart, $weekEnd])
            ->selectRaw('user_id, COUNT(*) as count, users.name')
            ->join('users', 'attendances.user_id', '=', 'users.id')
            ->groupBy('user_id', 'users.name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'count' => $item->count
                ];
            })
            ->toArray();

        // Get recent QR codes
        $recentQrCodes = \App\Models\QrCode::with('officeLocation')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalEmployees',
            'presentToday',
            'lateToday',
            'absentToday',
            'activeEmployees',
            'departmentsCount',
            'activeOffices',
            'recentAttendance',
            'recentLeaveRequests',
            'topLateArrivals',
            'recentQrCodes'
        ));
    }
}