@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4">My Leave Requests</h1>
    <p class="lead">View and manage your leave requests.</p>
    
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('employee.leave-requests.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Request New Leave
            </a>
        </div>
    </div>
    
    <!-- Leave Balance -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Annual Leave Balance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $leaveBalance ?? 0 }} days</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bed fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Used This Year</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $usedLeave ?? 0 }} days</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Pending Requests</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingRequests ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Approved This Year</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $approvedRequests ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- All Leave Requests -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">All Leave Requests</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
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
                                <td>{{ $request->created_at->format('M d, Y') }}</td>
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
                                <td colspan="7" class="text-center">No leave requests found.</td>
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