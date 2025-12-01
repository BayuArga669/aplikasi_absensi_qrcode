<?php

// app/Services/AttendanceService.php
namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use App\Models\QrCode;
use App\Models\WorkSchedule;
use App\Models\LeaveRequest;
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
        
        if (!$officeLocation) {
            return [
                'success' => false,
                'message' => 'Invalid QR code: no associated office location found',
            ];
        }
        
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
        $checkInDeadlineValue = $officeLocation->check_in_deadline;
        if (!$checkInDeadlineValue) {
            $checkInDeadlineValue = '09:00:00'; // Default to 9 AM if not set
        }
        $deadlineTime = Carbon::createFromFormat('H:i:s', $checkInDeadlineValue);
        if (!$deadlineTime) {
            $deadlineTime = Carbon::today()->setTime(9, 0, 0); // Default to 9 AM
        }
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
        QrCode $qrCode,
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

        // Validate location for check-out
        $officeLocation = $qrCode->officeLocation;

        if (!$officeLocation) {
            return [
                'success' => false,
                'message' => 'Invalid QR code: no associated office location found',
            ];
        }

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

        $currentTime = now();

        // Check if user is trying to check out before the check-out time
        // Get the office's check-out deadline (the earliest time users can check out)
        $checkOutTimeValue = $officeLocation->check_out_deadline;
        if (!$checkOutTimeValue) {
            $checkOutTimeValue = '17:00:00'; // Default to 5 PM if not set
        }
        $officeCheckOutTime = Carbon::createFromFormat('H:i:s', $checkOutTimeValue);
        if (!$officeCheckOutTime) {
            $officeCheckOutTime = Carbon::today()->setTime(17, 0, 0); // Default to 5 PM
        }
        $todayCheckOutTime = Carbon::today()->setTime($officeCheckOutTime->hour, $officeCheckOutTime->minute, 0);

        // Prevent check-out before the designated check-out time
        if ($currentTime->lt($todayCheckOutTime)) {
            return [
                'success' => false,
                'message' => 'Check-out is not allowed before ' . $todayCheckOutTime->format('H:i'),
            ];
        }

        // Check if the QR code used for check-out matches the one used for check-in
        // The business rule: before the check-out deadline, same QR code required; after deadline, any QR code allowed
        $checkOutDeadlineValue = $officeLocation->check_out_deadline; // Reuse the deadline for QR validation logic
        if (!$checkOutDeadlineValue) {
            $checkOutDeadlineValue = '17:00:00'; // Default to 5 PM if not set
        }
        $officeCheckOutDeadline = Carbon::createFromFormat('H:i:s', $checkOutDeadlineValue);
        if (!$officeCheckOutDeadline) {
            $officeCheckOutDeadline = Carbon::today()->setTime(17, 0, 0); // Default to 5 PM
        }
        $todayCheckOutDeadline = Carbon::today()->setTime($officeCheckOutDeadline->hour, $officeCheckOutDeadline->minute, 0);

        // If current time is before the check-out deadline, ensure the same QR code is used
        if ($currentTime->lt($todayCheckOutDeadline)) {
            if ($attendance->qr_code_id !== $qrCode->id) {
                return [
                    'success' => false,
                    'message' => 'You must use the same QR code for check-out that was used for check-in until after the check-out deadline',
                ];
            }
        }

        // Only update the QR code ID if it's different from the check-in QR code
        // This allows for using the same QR code or a different one for check-out
        $updateData = [
            'check_out_time' => $currentTime,
            'check_out_latitude' => $latitude,
            'check_out_longitude' => $longitude,
        ];

        // Update QR code ID only if it's different from check-in QR code
        if ($attendance->qr_code_id !== $qrCode->id) {
            $updateData['qr_code_id'] = $qrCode->id;
        }

        $attendance->update($updateData);

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

        // Count present employees (those with check-in records on the specified date)
        $present = Attendance::whereDate('check_in_time', $date)
            ->whereIn('status', ['on_time', 'late'])
            ->count();

        // Count late arrivals specifically
        $late = Attendance::whereDate('check_in_time', $date)
            ->where('status', 'late')
            ->count();

        $onLeave = LeaveRequest::where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->count();

        // Calculate absent as remaining employees after accounting for present and on leave
        // This includes employees who didn't check in at all (will be handled by our scheduled command later)
        $absent = $total - $present - $onLeave;

        return [
            'total' => $total,
            'present' => $present,
            'late' => $late,
            'on_leave' => $onLeave,
            'absent' => $absent,
        ];
    }
}
