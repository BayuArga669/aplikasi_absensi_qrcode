<?php

// app/Services/AttendanceService.php
namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use App\Models\QrCode;
use App\Models\WorkSchedule;
use Carbon\Carbon;

class AttendanceService
{
    protected $geolocationService;
    protected $notificationService;

    public function __construct(
        GeolocationService $geolocationService,
        NotificationService $notificationService
    ) {
        $this->geolocationService = $geolocationService;
        $this->notificationService = $notificationService;
    }

    public function checkIn(
        User $user,
        QrCode $qrCode,
        float $latitude,
        float $longitude
    ): array {
        // Validate location
        $officeLocation = $qrCode->officeLocation;
        
        if (!$this->geolocationService->isWithinRadius(
            $latitude,
            $longitude,
            $officeLocation->latitude,
            $officeLocation->longitude,
            $officeLocation->radius
        )) {
            return [
                'success' => false,
                'message' => 'You are outside the office area',
            ];
        }

        // Check if already checked in today
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', today())
            ->first();

        if ($existingAttendance) {
            return [
                'success' => false,
                'message' => 'You have already checked in today',
            ];
        }

        // Get user's work schedule
        $schedule = $this->getUserSchedule($user);
        
        // Get office location check-in deadline
        $officeLocation = $qrCode->officeLocation;
        
        // Check if late based on office deadline
        $checkInTime = now();
        $deadlineTime = Carbon::createFromFormat('H:i:s', $officeLocation->check_in_deadline);
        $isLate = $checkInTime->gt($deadlineTime);
        $lateDuration = $isLate ? $checkInTime->diffInMinutes($deadlineTime) : 0;

        // Create attendance record
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'qr_code_id' => $qrCode->id,
            'check_in_time' => $checkInTime,
            'check_in_latitude' => $latitude,
            'check_in_longitude' => $longitude,
            'status' => $isLate ? 'late' : 'on_time',
            'is_late' => $isLate,
            'late_duration' => $lateDuration,
        ]);

        // Send notification to superior if late
        if ($isLate && $user->superior_id) {
            $this->notificationService->notifyLateArrival($user, $attendance);
        }

        return [
            'success' => true,
            'message' => 'Check-in successful',
            'attendance' => $attendance,
            'is_late' => $isLate,
        ];
    }

    public function checkOut(
        User $user,
        float $latitude,
        float $longitude
    ): array {
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('check_in_time', today())
            ->whereNull('check_out_time')
            ->first();

        if (!$attendance) {
            return [
                'success' => false,
                'message' => 'No check-in record found for today',
            ];
        }

        $attendance->update([
            'check_out_time' => now(),
            'check_out_latitude' => $latitude,
            'check_out_longitude' => $longitude,
        ]);

        return [
            'success' => true,
            'message' => 'Check-out successful',
            'attendance' => $attendance,
        ];
    }

    protected function getUserSchedule(User $user): WorkSchedule
    {
        $userSchedule = $user->userSchedules()
            ->where('effective_date', '<=', today())
            ->orderBy('effective_date', 'desc')
            ->first();

        if ($userSchedule) {
            return $userSchedule->workSchedule;
        }

        return WorkSchedule::where('is_default', true)->firstOrFail();
    }

    public function getAttendanceStats(Carbon $date = null): array
    {
        $date = $date ?? today();

        $total = User::where('role', 'employee')
            ->where('is_active', true)
            ->count();

        $present = Attendance::whereDate('check_in_time', $date)->count();
        
        $late = Attendance::whereDate('check_in_time', $date)
            ->where('is_late', true)
            ->count();

        $onLeave = LeaveRequest::where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->count();

        return [
            'total' => $total,
            'present' => $present,
            'late' => $late,
            'on_leave' => $onLeave,
            'absent' => $total - $present - $onLeave,
        ];
    }
}
