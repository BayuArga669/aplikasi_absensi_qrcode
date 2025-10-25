<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;

class NotificationService
{
    public function notifyLateArrival(User $user, Attendance $attendance): void
    {
        if (!$user->superior) {
            return;
        }

        Notification::create([
            'user_id' => $user->superior_id,
            'title' => 'Late Arrival Alert',
            'message' => "{$user->name} checked in late at {$attendance->check_in_time->format('H:i')} ({$attendance->late_duration} minutes late)",
            'type' => 'late_arrival',
            'related_id' => $attendance->id,
        ]);
    }

    public function notifyLeaveRequest(LeaveRequest $leaveRequest): void
    {
        $admins = User::where('role', 'admin')->where('is_active', true)->get();

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'New Leave Request',
                'message' => "{$leaveRequest->user->name} has submitted a {$leaveRequest->type} request from {$leaveRequest->start_date->format('d M Y')} to {$leaveRequest->end_date->format('d M Y')}",
                'type' => 'leave_request',
                'related_id' => $leaveRequest->id,
            ]);
        }
    }

    public function notifyLeaveApproval(LeaveRequest $leaveRequest): void
    {
        Notification::create([
            'user_id' => $leaveRequest->user_id,
            'title' => 'Leave Request Approved',
            'message' => "Your {$leaveRequest->type} request has been approved",
            'type' => 'leave_approved',
            'related_id' => $leaveRequest->id,
        ]);
    }

    public function notifyLeaveRejection(LeaveRequest $leaveRequest): void
    {
        Notification::create([
            'user_id' => $leaveRequest->user_id,
            'title' => 'Leave Request Rejected',
            'message' => "Your {$leaveRequest->type} request has been rejected. Reason: {$leaveRequest->rejection_reason}",
            'type' => 'leave_rejected',
            'related_id' => $leaveRequest->id,
        ]);
    }
}