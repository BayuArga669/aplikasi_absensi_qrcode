<?php

namespace App\Http\Controllers\Superior;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class LateReportController extends Controller
{
    public function index(Request $request)
    {
        $authUser = auth()->user();
        $teamMembers = User::where('superior_id', $authUser->id)->get();
        
        $query = Attendance::where('status', 'late')
            ->whereIn('user_id', $teamMembers->pluck('id'));
        
        if ($request->filled('start_date')) {
            $query->whereDate('check_in_time', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('check_in_time', '<=', $request->end_date);
        }
        
        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }
        
        $lateArrivals = $query->orderBy('check_in_time', 'desc')->paginate(20);
        
        // Calculate statistics
        $totalLateArrivals = $query->count();
        $totalLateMinutes = 0;
        $teamMembersLate = collect();
        
        foreach ($lateArrivals as $late) {
            if ($late->check_in_time) {
                $scheduledTime = Carbon::parse(config('app.check_in_start_time', '08:00'));
                $checkInTime = Carbon::parse($late->check_in_time);
                
                if ($checkInTime->gt($scheduledTime)) {
                    $totalLateMinutes += $scheduledTime->diffInMinutes($checkInTime);
                }
                
                $teamMembersLate->push($late->user_id);
            }
        }
        
        $avgLateTime = $totalLateArrivals > 0 ? 
            gmdate('H:i', ($totalLateMinutes / $totalLateArrivals) * 60) : '00:00';
        
        return view('superior.late_reports', compact(
            'lateArrivals', 
            'teamMembers',
            'totalLateArrivals', 
            'totalLateMinutes', 
            'avgLateTime',
            'teamMembersLate'
        ));
    }
}