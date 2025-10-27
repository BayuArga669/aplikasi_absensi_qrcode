<?php

namespace App\Http\Controllers\Superior;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class SuperiorDashboardController extends Controller
{
    public function index()
    {
        $authUser = auth()->user();
        
        // Get team size
        $teamSize = User::where('superior_id', $authUser->id)->count();
        
        // Get today's attendance stats for team
        $teamIds = User::where('superior_id', $authUser->id)->pluck('id');
        
        $presentToday = Attendance::whereDate('check_in_time', Carbon::today())
            ->whereIn('user_id', $teamIds)
            ->where('status', 'on_time')
            ->count();
            
        $lateToday = Attendance::whereDate('check_in_time', Carbon::today())
            ->whereIn('user_id', $teamIds)
            ->where('status', 'late')
            ->count();
            
        $absentToday = count($teamIds) - ($presentToday + $lateToday);
        
        // Get recent late arrivals
        $recentLateArrivals = Attendance::with('user')
            ->whereDate('check_in_time', Carbon::today())
            ->whereIn('user_id', $teamIds)
            ->where('status', 'late')
            ->orderBy('check_in_time', 'desc')
            ->limit(10)
            ->get();
            
        // Get team attendance for overview
        $teamAttendance = User::with(['attendances' => function($query) {
            $query->whereDate('check_in_time', Carbon::today());
        }])
        ->where('superior_id', $authUser->id)
        ->get();
        
        foreach ($teamAttendance as $member) {
            $todayAttendance = $member->attendances->first();
            $member->attendance_status = $todayAttendance ? $todayAttendance->status : 'absent';
            $member->check_in_time = $todayAttendance ? $todayAttendance->check_in_time : null;
            $member->check_out_time = $todayAttendance ? $todayAttendance->check_out_time : null;
            
            // Calculate attendance rate for this month
            $monthStart = Carbon::now()->startOfMonth();
            $monthEnd = Carbon::now()->endOfMonth();
            
            $present = $member->attendances()
                ->whereBetween('check_in_time', [$monthStart, $monthEnd])
                ->where('status', 'on_time')
                ->count();
                
            $late = $member->attendances()
                ->whereBetween('check_in_time', [$monthStart, $monthEnd])
                ->where('status', 'late')
                ->count();
                
            $total = $present + $late; // Only count present and late for rate
            $member->attendance_rate = $total > 0 ? round(($present + $late) / (Carbon::now()->day) * 100, 1) : 0;
        }

        return view('superior.dashboard', compact(
            'teamSize',
            'presentToday',
            'lateToday',
            'absentToday',
            'recentLateArrivals',
            'teamAttendance'
        ));
    }

    public function teamAttendance()
    {
        // This can be used for team attendance specific view if needed
        return $this->index();
    }

    public function lateArrivals()
    {
        // This can be used for late arrivals specific view if needed
        return $this->index();
    }
}