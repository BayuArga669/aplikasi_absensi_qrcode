@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4">Leave/Sick Requests</h1>
    <p class="lead">Manage employee leave and sick requests.</p>
    
    <!-- Pending Requests -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-warning">Pending Requests</h6>
        </div>
        <div class="card-body">
            @if($pendingRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Dates</th>
                                <th>Reason</th>
                                <th>Attachments</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingRequests as $request)
                                <tr>
                                    <td>{{ $request->employee->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge 
                                            @if($request->type === 'leave') bg-info
                                            @elseif($request->type === 'sick') bg-warning
                                            @else bg-secondary @endif">
                                            {{ ucfirst($request->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $request->start_date ? $request->start_date->format('d M Y') : '-' }} to {{ $request->end_date ? $request->end_date->format('d M Y') : '-' }}</td>
                                    <td>{{ $request->reason }}</td>
                                    <td>
                                        @if($request->attachment)
                                            <a href="{{ asset('storage/' . $request->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-file me-1"></i>View
                                            </a>
                                        @else
                                            <span class="text-muted">None</span>
                                        @endif
                                    </td>
                                    <td>{{ $request->created_at ? $request->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') : '-' }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <form action="{{ route('admin.leave-requests.approve', $request->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this request?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.leave-requests.reject', $request->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Reject this request?')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">No pending requests.</p>
            @endif
        </div>
    </div>
    
    <!-- All Requests -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">All Requests</h6>
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
                            <th>Processed</th>
                            <th>Processed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allRequests as $request)
                            <tr>
                                <td>{{ $request->employee->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge 
                                        @if($request->type === 'leave') bg-info
                                        @elseif($request->type === 'sick') bg-warning
                                        @else bg-secondary @endif">
                                        {{ ucfirst($request->type) }}
                                    </span>
                                </td>
                                <td>{{ $request->start_date ? $request->start_date->format('d M Y') : '-' }} to {{ $request->end_date ? $request->end_date->format('d M Y') : '-' }}</td>
                                <td>{{ $request->reason }}</td>
                                <td>
                                    <span class="badge 
                                        @if($request->status === 'pending') bg-warning
                                        @elseif($request->status === 'approved') bg-success
                                        @else bg-danger @endif">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td>{{ $request->created_at ? $request->created_at->timezone('Asia/Jakarta')->format('d M Y') : '-' }}</td>
                                <td>
                                    @if($request->processed_at)
                                        {{ $request->processed_at->format('M d, Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $request->processedBy->name ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                
                @if($allRequests->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $allRequests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection