@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4">Late Arrival Reports</h1>
    <p class="lead">Detailed reports on team member late arrivals.</p>
    
    <!-- Report Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Report Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('superior.late-reports') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="employee_id" class="form-label">Team Member</label>
                        <select class="form-control" id="employee_id" name="employee_id">
                            <option value="">All Team Members</option>
                            @foreach($teamMembers as $member)
                                <option value="{{ $member->id }}" {{ request('employee_id') == $member->id ? 'selected' : '' }}>
                                    {{ $member->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('superior.late-reports') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Late Summary -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Late Arrivals</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalLateArrivals ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Total Late Minutes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalLateMinutes ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Avg. Late Time</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $avgLateTime ?? '00:00' }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calculator fa-2x text-gray-300"></i>
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
                                Team Members Late</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $teamMembersLate ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Late Arrivals Details -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Late Arrivals Details</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Scheduled Time</th>
                            <th>Check-in Time</th>
                            <th>Late Duration</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lateArrivals as $late)
                            <tr>
                                <td>{{ $late->user->name ?? 'N/A' }}</td>
                                <td>{{ $late->check_in_time->format('Y-m-d') }}</td>
                                <td>{{ config('app.check_in_start_time', '08:00') }}</td>
                                <td>{{ $late->check_in_time }}</td>
                                <td>
                                    <span class="badge bg-warning">
                                        {{ $late->late_duration ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>N/A</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No late arrivals found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                
                @if($lateArrivals->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $lateArrivals->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection