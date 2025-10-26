<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get today's attendance
        $todayAttendance = $user->attendances()
            ->whereDate('check_in_time', Carbon::today())
            ->first();
        
        // Get recent attendance
        $recentAttendance = $user->attendances()
            ->orderBy('check_in_time', 'desc')
            ->limit(5)
            ->get();
        
        // Calculate statistics
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        
        $presentCount = $user->attendances()
            ->whereBetween('check_in_time', [$monthStart, $monthEnd])
            ->where('status', 'present')
            ->count();
            
        $lateCount = $user->attendances()
            ->whereBetween('check_in_time', [$monthStart, $monthEnd])
            ->where('status', 'late')
            ->count();
            
        $absentCount = $user->attendances()
            ->whereBetween('check_in_time', [$monthStart, $monthEnd])
            ->where('status', 'absent')
            ->count();
            
        $totalWorkingDays = $presentCount + $lateCount + $absentCount;
        $attendanceRate = $totalWorkingDays > 0 ? round(($presentCount + $lateCount) / $totalWorkingDays * 100, 1) : 0;
        
        // Leave balance (example)
        $leaveBalance = 12; // This would come from a leave management system
        $usedLeave = 3; // This would come from a leave management system
        $pendingLeave = $user->leaveRequests()
            ->where('status', 'pending')
            ->count();
        
        $workingDays = Carbon::now()->day;
        
        return view('employee.dashboard', compact(
            'todayAttendance',
            'recentAttendance',
            'attendanceRate',
            'lateCount',
            'workingDays',
            'leaveBalance',
            'pendingLeave'
        ));
    }

    public function profile()
    {
        $user = auth()->user();
        return view('employee.profile.show', compact('user'));
    }

    public function editProfile()
    {
        $user = auth()->user();
        return view('employee.profile.edit', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'password' => 'nullable|min:8|confirmed',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'position' => $request->position,
            'department' => $request->department,
        ];

        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return redirect()->route('employee.profile')->with('success', 'Profile updated successfully.');
    }
}