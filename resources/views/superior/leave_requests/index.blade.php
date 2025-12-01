@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4">Team Leave Requests</h1>
    <p class="lead">Manage leave requests from your team members.</p>

    <!-- Leave Requests Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Leave Requests</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Dates</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaveRequests as $request)
                            <tr>
                                <td>{{ $request->user->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge
                                        @if($request->type === 'leave') bg-info
                                        @elseif($request->type === 'sick') bg-warning
                                        @else bg-secondary @endif">
                                        {{ ucfirst($request->type) }}
                                    </span>
                                </td>
                                <td>{{ $request->start_date }} to {{ $request->end_date }}</td>
                                <td>{{ Str::limit($request->reason, 30) }}</td>
                                <td>
                                    <span class="badge
                                        @if($request->status === 'pending') bg-warning
                                        @elseif($request->status === 'approved') bg-success
                                        @else bg-danger @endif">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td>{{ $request->created_at ? $request->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') : '-' }}</td>
                                <td>
                                    @if($request->status === 'pending')
                                        <div class="btn-group btn-group-sm" role="group">
                                            <form action="{{ route('superior.leave-requests.approve', $request->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to approve this leave request?')">
                                                    Approve
                                                </button>
                                            </form>
                                            <form action="{{ route('superior.leave-requests.reject', $request->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to reject this leave request?')">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No leave requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                
                @if($leaveRequests->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $leaveRequests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection