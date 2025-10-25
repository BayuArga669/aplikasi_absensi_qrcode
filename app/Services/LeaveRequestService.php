<?php

// app/Services/LeaveRequestService.php
namespace App\Services;

use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

class LeaveRequestService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function createLeaveRequest(
        User $user,
        string $type,
        Carbon $startDate,
        Carbon $endDate,
        string $reason,
        ?UploadedFile $attachment = null
    ): LeaveRequest {
        $daysCount = $startDate->diffInDays($endDate) + 1;

        $attachmentPath = null;
        if ($attachment) {
            $attachmentPath = $attachment->store('leave_attachments', 'public');
        }

        $leaveRequest = LeaveRequest::create([
            'user_id' => $user->id,
            'type' => $type,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_count' => $daysCount,
            'reason' => $reason,
            'attachment_path' => $attachmentPath,
            'status' => 'pending',
        ]);

        $this->notificationService->notifyLeaveRequest($leaveRequest);

        return $leaveRequest;
    }

    public function approveLeaveRequest(LeaveRequest $leaveRequest, User $approver): void
    {
        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        $this->notificationService->notifyLeaveApproval($leaveRequest);
    }

    public function rejectLeaveRequest(
        LeaveRequest $leaveRequest,
        User $approver,
        string $reason
    ): void {
        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $this->notificationService->notifyLeaveRejection($leaveRequest);
    }
}
