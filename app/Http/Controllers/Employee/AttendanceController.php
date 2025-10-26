<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function scan()
    {
        $user = auth()->user();
        
        // Check if user has already checked in today
        $todayAttendance = $user->attendances()
            ->whereDate('check_in_time', Carbon::today())
            ->first();
        
        return view('employee.attendance.scan', compact('todayAttendance'));
    }

    public function checkIn(Request $request)
    {
        // This would be implemented to handle QR code scanning and attendance recording
        // For now, we'll just return a response
        return response()->json(['status' => 'success', 'message' => 'Check-in recorded']);
    }

    public function history(Request $request)
    {
        $user = auth()->user();
        
        $query = $user->attendances();
        
        if ($request->filled('start_date')) {
            $query->whereDate('check_in_time', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('check_in_time', '<=', $request->end_date);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $attendanceRecords = $query->orderBy('check_in_time', 'desc')->paginate(20);
        
        // Calculate statistics
        $presentCount = $user->attendances()->where('status', 'present')->count();
        $lateCount = $user->attendances()->where('status', 'late')->count();
        $absentCount = $user->attendances()->where('status', 'absent')->count();
        
        $totalRecords = $presentCount + $lateCount + $absentCount;
        $attendanceRate = $totalRecords > 0 ? round(($presentCount + $lateCount) / $totalRecords * 100, 1) . '%' : '0%';
        
        // Calculate monthly summary
        $monthlySummary = [];
        $attendances = $user->attendances()
            ->selectRaw('YEAR(check_in_time) as year, MONTH(check_in_time) as month, status, COUNT(*) as count')
            ->groupBy('year', 'month', 'status')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
        
        $monthlyData = [];
        foreach ($attendances as $attendance) {
            $key = $attendance->year . '-' . str_pad($attendance->month, 2, '0', STR_PAD_LEFT);
            if (!isset($monthlyData[$key])) {
                $monthlyData[$key] = ['present' => 0, 'late' => 0, 'absent' => 0];
            }
            $monthlyData[$key][$attendance->status] = $attendance->count;
        }
        
        foreach ($monthlyData as $date => $data) {
            $present = $data['present'];
            $late = $data['late'];
            $absent = $data['absent'];
            $total = $present + $late + $absent;
            $rate = $total > 0 ? round(($present + $late) / $total * 100, 1) : 0;
            
            $monthlySummary[] = [
                'month' => Carbon::createFromFormat('Y-m', $date)->format('F Y'),
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'rate' => $rate
            ];
        }
        
        return view('employee.attendance.history', compact(
            'attendanceRecords',
            'presentCount',
            'lateCount',
            'absentCount',
            'attendanceRate',
            'monthlySummary'
        ));
    }
}