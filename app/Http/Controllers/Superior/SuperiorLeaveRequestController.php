<?php

namespace App\Http\Controllers\Superior;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;

class SuperiorLeaveRequestController extends Controller
{
    public function index()
    {
        $authUser = auth()->user();

        // Get team members (employees under this superior)
        $teamMemberIds = User::where('superior_id', $authUser->id)->pluck('id');

        // Get leave requests from team members
        $leaveRequests = LeaveRequest::whereIn('user_id', $teamMemberIds)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('superior.leave_requests.index', compact('leaveRequests'));
    }

    public function approve($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        // Verify that the authenticated user is the superior of the employee who made the request
        $employee = User::findOrFail($leaveRequest->user_id);
        if ($employee->superior_id != auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Only allow approval if status is pending
        if ($leaveRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Leave request is not pending.');
        }

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(), // Superior yang menyetujui
            'approved_at' => Carbon::now() // Waktu disetujui
        ]);

        return redirect()->back()->with('success', 'Leave request approved successfully.');
    }

    public function reject($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        // Verify that the authenticated user is the superior of the employee who made the request
        $employee = User::findOrFail($leaveRequest->user_id);
        if ($employee->superior_id != auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Only allow rejection if status is pending
        if ($leaveRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Leave request is not pending.');
        }

        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(), // Superior yang menolak
            'approved_at' => Carbon::now(), // Waktu ditolak
            'rejection_reason' => 'Rejected by superior' // Alasan penolakan
        ]);

        return redirect()->back()->with('success', 'Leave request rejected successfully.');
    }
}