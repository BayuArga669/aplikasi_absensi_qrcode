<?php
// app/Http/Controllers/Api/LeaveRequestController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LeaveRequestService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    protected $leaveRequestService;

    public function __construct(LeaveRequestService $leaveRequestService)
    {
        $this->leaveRequestService = $leaveRequestService;
    }

    public function index(Request $request)
    {
        $leaveRequests = $request->user()
            ->leaveRequests()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $leaveRequests,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:leave,sick',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $leaveRequest = $this->leaveRequestService->createLeaveRequest(
            $request->user(),
            $request->type,
            Carbon::parse($request->start_date),
            Carbon::parse($request->end_date),
            $request->reason,
            $request->file('attachment')
        );

        return response()->json([
            'success' => true,
            'message' => 'Leave request submitted successfully',
            'data' => $leaveRequest,
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $leaveRequest = $request->user()
            ->leaveRequests()
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $leaveRequest,
        ]);
    }
}