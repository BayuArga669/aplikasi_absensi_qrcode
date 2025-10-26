<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $allRequests = $user->leaveRequests()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        $pendingRequests = $user->leaveRequests()
            ->where('status', 'pending')
            ->count();
            
        $usedLeave = $user->leaveRequests()
            ->where('status', 'approved')
            ->whereBetween('start_date', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])
            ->sum('days_count');
            
        $leaveBalance = 12; // Example annual leave balance
        $approvedRequests = $user->leaveRequests()
            ->where('status', 'approved')
            ->whereBetween('start_date', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])
            ->count();
        
        return view('employee.leave_requests.index', compact(
            'allRequests',
            'pendingRequests',
            'usedLeave',
            'leaveBalance',
            'approvedRequests'
        ));
    }

    public function create()
    {
        $user = auth()->user();
        
        $pendingRequests = $user->leaveRequests()
            ->where('status', 'pending')
            ->count();
            
        $usedLeave = $user->leaveRequests()
            ->where('status', 'approved')
            ->whereBetween('start_date', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])
            ->sum('days_count');
            
        $leaveBalance = 12; // Example annual leave balance
        
        $recentRequests = $user->leaveRequests()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('employee.leave_requests.create', compact(
            'pendingRequests',
            'usedLeave',
            'leaveBalance',
            'recentRequests'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:leave,sick,other',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);
        
        $user = auth()->user();
        
        // Calculate number of days
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $daysCount = $startDate->diffInDays($endDate) + 1;
        
        // Handle file upload if exists
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave_attachments', 'public');
        }
        
        $leaveRequest = LeaveRequest::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'attachment' => $attachmentPath,
            'days_count' => $daysCount,
            'status' => 'pending',
        ]);
        
        return redirect()->route('employee.leave-requests.index')
            ->with('success', 'Leave request submitted successfully.');
    }
}