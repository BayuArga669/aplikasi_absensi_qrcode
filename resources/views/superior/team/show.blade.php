@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4">Team Member Details</h1>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <i class="fas fa-user fa-5x text-gray-300"></i>
                    <h5 class="mt-3">{{ $teamMember->name }}</h5>
                    <p class="text-muted">{{ $teamMember->position }}</p>
                    
                    <div class="mt-3">
                        <p><strong>Email:</strong> {{ $teamMember->email }}</p>
                        <p><strong>Phone:</strong> {{ $teamMember->phone ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Attendance</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $attendance)
                                    <tr>
                                        <td>{{ $attendance->check_in_time ? $attendance->check_in_time->format('Y-m-d') : '-' }}</td>
                                        <td>{{ $attendance->check_in_time ? $attendance->check_in_time->timezone('Asia/Jakarta')->format('d M Y H:i') : '-' }}</td>
                                        <td>{{ $attendance->check_out_time ? $attendance->check_out_time->timezone('Asia/Jakarta')->format('d M Y H:i') : '-' }}</td>
                                        <td>
                                            <span class="badge
                                                @if($attendance->status === 'on_time') bg-success
                                                @elseif($attendance->status === 'late') bg-warning
                                                @else bg-danger @endif">
                                                @if($attendance->status === 'on_time')
                                                    <i class="fas fa-check me-1"></i>On Time
                                                @elseif($attendance->status === 'late')
                                                    <i class="fas fa-clock me-1"></i>Late
                                                @else
                                                    <i class="fas fa-times me-1"></i>{{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                                                @endif
                                            </span>
                                        </td>
                                        <td>{{ $attendance->check_in_latitude ?? 'N/A' }}, {{ $attendance->check_in_longitude ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No attendance records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Leave Requests</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
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
                                                        <button type="submit" class="btn btn-success btn-sm">
                                                            Approve
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('superior.leave-requests.reject', $request->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-danger btn-sm">
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
                                        <td colspan="6" class="text-center">No leave requests found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="{{ route('superior.team.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Team List
        </a>
    </div>
</div>
@endsection