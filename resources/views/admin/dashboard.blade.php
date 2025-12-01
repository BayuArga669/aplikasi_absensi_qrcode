@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Admin Dashboard</h1>
        <a href="{{ route('admin.reports.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
        </a>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Total Employees Card -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Employees</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalEmployees ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Present Today Card -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Present Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $presentToday ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Late Today Card -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Late Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $lateToday ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Absent Today Card -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Absent Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $absentToday ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Employee Distribution -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Active Employees</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeEmployees ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employees by Department -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Employee Departments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $departmentsCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Offices -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-dark shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                Active Offices</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeOffices ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-map-marker-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Recent Activity -->
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Attendance</h6>
                    <a href="{{ route('admin.reports.index') }}" class="m-0 font-weight-bold text-primary">View All</a>
                </div>
                <div class="card-body">
                    @if(isset($recentAttendance) && count($recentAttendance) > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($recentAttendance as $attendance)
                                <li class="list-group-item">
                                    <div class="row">
                                        <div class="col-12 col-sm-8">
                                            <div class="font-weight-bold">
                                                {{ $attendance->user->name ?? 'N/A' }}
                                            </div>
                                            <div class="text-muted small">
                                                Check-in: {{ $attendance->check_in_time ? $attendance->check_in_time->timezone('Asia/Jakarta')->format('d M Y H:i') : '-' }}
                                                @if($attendance->status === 'late')
                                                    <span class="badge badge-warning">Late</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-4 text-right mt-2 mt-sm-0">
                                            <span class="badge
                                                @if($attendance->status === 'on_time') badge-success
                                                @elseif($attendance->status === 'late') badge-warning
                                                @else badge-danger @endif">
                                                @if($attendance->status === 'on_time')
                                                    <i class="fas fa-check me-1"></i>On Time
                                                @elseif($attendance->status === 'late')
                                                    <i class="fas fa-clock me-1"></i>Late
                                                @else
                                                    <i class="fas fa-times me-1"></i>{{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted text-center">No recent attendance records found.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Leave Requests -->
        <div class="col-lg-6 col-md-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-warning">Recent Leave Requests</h6>
                    <a href="{{ route('admin.leave-requests') }}" class="m-0 font-weight-bold text-warning">View All</a>
                </div>
                <div class="card-body">
                    @if(isset($recentLeaveRequests) && count($recentLeaveRequests) > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($recentLeaveRequests as $request)
                                <li class="list-group-item">
                                    <div class="row">
                                        <div class="col-12 col-sm-8">
                                            <div class="font-weight-bold">
                                                {{ $request->user->name ?? 'N/A' }}
                                            </div>
                                            <div class="text-muted small">
                                                {{ $request->type }} - {{ $request->start_date }} to {{ $request->end_date }}
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-4 text-right mt-2 mt-sm-0">
                                            <span class="badge
                                                @if($request->status === 'pending') badge-warning
                                                @elseif($request->status === 'approved') badge-success
                                                @else badge-danger @endif">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted text-center">No recent leave requests.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Top Late Arrivals -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-danger">Top Late Arrivals (This Week)</h6>
                </div>
                <div class="card-body">
                    @if(isset($topLateArrivals) && count($topLateArrivals) > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($topLateArrivals as $late)
                                <li class="list-group-item">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="font-weight-bold">
                                                {{ $late['name'] ?? 'N/A' }}
                                            </div>
                                            <div class="text-muted small">
                                                Late Arrivals: {{ $late['count'] }}
                                            </div>
                                        </div>
                                        <div class="col-4 text-right">
                                            <span class="badge badge-danger">
                                                <i class="fas fa-clock"></i> {{ $late['count'] }}
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted text-center">No late arrivals this week.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- QR Code Activity -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-success">Recent QR Code Generations</h6>
                </div>
                <div class="card-body">
                    @if(isset($recentQrCodes) && count($recentQrCodes) > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($recentQrCodes as $qr)
                                <li class="list-group-item">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="font-weight-bold">
                                                {{ $qr->officeLocation->name ?? 'Office QR' }}
                                            </div>
                                            <div class="text-muted small">
                                                Generated: {{ $qr->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }}
                                            </div>
                                        </div>
                                        <div class="col-4 text-right">
                                            <span class="badge badge-success">
                                                <i class="fas fa-qrcode"></i> Active
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted text-center">No recent QR codes generated.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection