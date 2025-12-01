<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    public function scan()
    {
        $user = auth()->user();

        // Check if user has already checked in today
        $todayAttendance = $user->attendances()
            ->whereDate('check_in_time', Carbon::today())
            ->first();

        // Get the most common/primary office location (you might want to adjust this logic based on your business rules)
        $officeLocations = \App\Models\OfficeLocation::where('is_active', true)->get();
        $officeLocation = $officeLocations->first(); // Using first active office

        return view('employee.attendance.scan', compact('todayAttendance', 'officeLocation'));
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Use the API controller's services to process the check-in
        $qrCodeService = app(\App\Services\QrCodeService::class);
        $attendanceService = app(\App\Services\AttendanceService::class);

        // Validate QR code
        $qrCode = $qrCodeService->validateQrCode($request->qr_code);

        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired QR code',
            ], 400);
        }

        // Process check-in
        $result = $attendanceService->checkIn(
            auth()->user(),
            $qrCode,
            $request->latitude,
            $request->longitude
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Use the API controller's services to process the check-out
        $qrCodeService = app(\App\Services\QrCodeService::class);
        $attendanceService = app(\App\Services\AttendanceService::class);

        // Validate QR code
        $qrCode = $qrCodeService->validateQrCode($request->qr_code);

        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired QR code',
            ], 400);
        }

        // Process check-out
        $result = $attendanceService->checkOut(
            auth()->user(),
            $qrCode,
            $request->latitude,
            $request->longitude
        );

        return response()->json($result, $result['success'] ? 200 : 400);
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

        // Calculate statistics - applying the same filters as the main query
        $statsQuery = $user->attendances();

        if ($request->filled('start_date')) {
            $statsQuery->whereDate('check_in_time', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $statsQuery->whereDate('check_in_time', '<=', $request->end_date);
        }

        // Calculate all counts regardless of status filter for correct totals
        $baseQuery = $user->attendances();

        if ($request->filled('start_date')) {
            $baseQuery->whereDate('check_in_time', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $baseQuery->whereDate('check_in_time', '<=', $request->end_date);
        }

        // Get all counts for the total calculation
        $allPresentCount = $baseQuery->clone()->where('status', 'on_time')->count();
        $allLateCount = $baseQuery->clone()->where('status', 'late')->count();
        $allAbsentCount = $baseQuery->clone()->where('status', 'absent')->count();

        // Calculate individual counts only if no specific status filter is applied
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'on_time') {
                $presentCount = $baseQuery->clone()->where('status', 'on_time')->count();
                $lateCount = 0;
                $absentCount = 0;
            } elseif ($status === 'late') {
                $presentCount = 0;
                $lateCount = $baseQuery->clone()->where('status', 'late')->count();
                $absentCount = 0;
            } elseif ($status === 'absent') {
                $presentCount = 0;
                $lateCount = 0;
                $absentCount = $baseQuery->clone()->where('status', 'absent')->count();
            } else {
                $presentCount = 0;
                $lateCount = 0;
                $absentCount = 0;
            }
        } else {
            // No specific status filter, calculate all counts with same date filters
            $presentCount = $allPresentCount;
            $lateCount = $allLateCount;
            $absentCount = $allAbsentCount;
        }

        $totalRecords = $allPresentCount + $allLateCount + $allAbsentCount;
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

    public function exportHistory(Request $request)
    {
        $user = auth()->user();

        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $status = $request->status;

        // Prepare filters for export
        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'employee_id' => $user->id, // Only export current user's data
        ];

        // Create export instance with filters
        $export = new \App\Exports\AttendanceReportExport($filters);

        // Generate filename with timestamp
        $filename = 'my_attendance_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download($export, $filename);
    }
}