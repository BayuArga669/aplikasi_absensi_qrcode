<?php

namespace App\Http\Controllers\Superior;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class TeamController extends Controller
{
    public function index()
    {
        $authUser = auth()->user();
        $teamMembers = User::where('superior_id', $authUser->id)->paginate(10);
        
        foreach ($teamMembers as $member) {
            $todayAttendance = $member->attendances()
                ->whereDate('check_in_time', Carbon::today())
                ->first();
            
            $member->attendance_status = $todayAttendance ? $todayAttendance->status : 'absent';
            $member->check_in_time = $todayAttendance ? $todayAttendance->check_in_time : null;
            $member->check_out_time = $todayAttendance ? $todayAttendance->check_out_time : null;
            
            // Calculate attendance rate for this month
            $monthStart = Carbon::now()->startOfMonth();
            $monthEnd = Carbon::now()->endOfMonth();
            $totalDays = $monthStart->diffInDays($monthEnd) + 1;
            
            $presentCount = $member->attendances()
                ->whereBetween('check_in_time', [$monthStart, $monthEnd])
                ->where('status', 'on_time')
                ->count();
                
            $lateCount = $member->attendances()
                ->whereBetween('check_in_time', [$monthStart, $monthEnd])
                ->where('status', 'late')
                ->count();
                
            $member->attendance_rate = $totalDays > 0 ? ($presentCount + $lateCount) / $totalDays * 100 : 0;
        }
        
        return view('superior.team.index', compact('teamMembers'));
    }

    public function show($id)
    {
        // Show individual team member details
        $teamMember = User::findOrFail($id);
        $authUser = auth()->user();
        
        // Verify that this user is the superior of the requested employee
        if ($teamMember->superior_id != $authUser->id) {
            abort(403, 'Unauthorized');
        }
        
        $attendances = $teamMember->attendances()
            ->orderBy('check_in_time', 'desc')
            ->paginate(15);
            
        $leaveRequests = $teamMember->leaveRequests()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('superior.team.show', compact('teamMember', 'attendances', 'leaveRequests'));
    }
}