<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;

class LeaveRequestAdminController extends Controller
{
    public function index()
    {
        $pendingRequests = LeaveRequest::where('status', 'pending')->get();
        
        $allRequests = LeaveRequest::with(['user', 'approver'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('admin.leave_requests', compact('pendingRequests', 'allRequests'));
    }

    public function show($id)
    {
        $request = LeaveRequest::with(['user', 'approver'])->findOrFail($id);
        return view('admin.leave_requests.show', compact('request'));
    }

    public function approve($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        
        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => Carbon::now()
        ]);
        
        return redirect()->route('admin.leave-requests')->with('success', 'Leave request approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        
        $leaveRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason ?? 'Not specified'
        ]);
        
        return redirect()->route('admin.leave-requests')->with('success', 'Leave request rejected successfully.');
    }
}