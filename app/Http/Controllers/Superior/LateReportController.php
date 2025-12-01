<?php

namespace App\Http\Controllers\Superior;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

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

        foreach ($lateArrivals as $late) {
            if ($late->check_in_time) {
                $scheduledTime = Carbon::parse(config('app.check_in_start_time', '08:00'));
                $checkInTime = Carbon::parse($late->check_in_time);

                if ($checkInTime->gt($scheduledTime)) {
                    $totalLateMinutes += $scheduledTime->diffInMinutes($checkInTime);
                }
            }
        }

        $avgLateTime = $totalLateArrivals > 0 ?
            gmdate('H:i', ($totalLateMinutes / $totalLateArrivals) * 60) : '00:00';
        $totalLateTimeFormatted = gmdate('H:i', $totalLateMinutes * 60);

        return view('superior.late_reports', compact(
            'lateArrivals',
            'teamMembers',
            'totalLateArrivals',
            'totalLateMinutes',
            'avgLateTime',
            'totalLateTimeFormatted'
        ));
    }

    public function export(Request $request)
    {
        $authUser = auth()->user();

        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $employeeId = $request->employee_id;

        // Prepare filters for export
        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'employee_id' => $employeeId,
        ];

        // Create export instance with filters
        $export = new \App\Exports\LateReportExport($authUser->id, $filters);

        // Generate filename with timestamp
        $filename = 'late_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download($export, $filename);
    }
}