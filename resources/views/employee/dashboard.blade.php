@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Employee Dashboard</h1>
        @if($todayAttendance && !$todayAttendance->check_out_time)
            <a href="{{ route('employee.attendance.scan') }}" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
                <i class="fas fa-sign-out-alt fa-sm text-white-50"></i> QR Check-out
            </a>
        @else
            <a href="{{ route('employee.attendance.scan') }}" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
                <i class="fas fa-qrcode fa-sm text-white-50"></i> QR Check-in
            </a>
        @endif
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Attendance Rate Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                This Month Attendance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $attendanceRate ?? '0%' }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Late Arrivals Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Late Arrivals (This Month)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $lateCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Working Days Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Working Days</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $workingDays ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Remaining Leave Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Remaining Leave</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $remainingLeave ?? 0 }} days</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bed fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Today's Attendance Status -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Today's Attendance Status</h6>
                </div>
                <div class="card-body">
                    @if($todayAttendance)
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center">
                                <i class="fas fa-{{ $todayAttendance->status === 'on_time' ? 'check-circle text-success' : ($todayAttendance->status === 'late' ? 'clock text-warning' : 'times-circle text-danger') }} fa-5x"></i>
                            </div>
                            <div class="col-md-8">
                                <h4 class="text-{{ $todayAttendance->status === 'on_time' ? 'success' : ($todayAttendance->status === 'late' ? 'warning' : 'danger') }} mb-3">
                                    @if($todayAttendance->status === 'on_time')
                                        <i class="fas fa-check me-2"></i>On Time
                                    @elseif($todayAttendance->status === 'late')
                                        <i class="fas fa-clock me-2"></i>Late
                                    @else
                                        <i class="fas fa-times me-2"></i>{{ ucfirst(str_replace('_', ' ', $todayAttendance->status)) }}
                                    @endif
                                </h4>
                                <p class="mb-1"><strong>Check-in:</strong> {{ $todayAttendance->check_in_time ? $todayAttendance->check_in_time->timezone('Asia/Jakarta')->format('d M Y H:i') : 'N/A' }}</p>
                                @if($todayAttendance->check_out_time)
                                    <p class="mb-1"><strong>Check-out:</strong> {{ $todayAttendance->check_out_time->timezone('Asia/Jakarta')->format('d M Y H:i') }}</p>
                                @else
                                    <p class="mb-1"><strong>Status:</strong> <span class="badge bg-warning">Not Checked Out</span></p>
                                    <a href="{{ route('employee.attendance.scan') }}" class="btn btn-success mt-2">
                                        <i class="fas fa-sign-out-alt me-2"></i>QR Check-out
                                    </a>
                                @endif
                                <p class="text-muted mb-0"><strong>Location:</strong> {{ $todayAttendance->check_in_latitude ?? 'N/A' }}, {{ $todayAttendance->check_in_longitude ?? 'N/A' }}</p>
                            </div>
                        </div>
                    @else
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center">
                                <i class="fas fa-clock fa-5x text-secondary"></i>
                            </div>
                            <div class="col-md-8">
                                <h4 class="text-secondary mb-3">Not Checked In</h4>
                                <p class="text-muted">Scan the QR code at the office to check in</p>
                                <a href="{{ route('employee.attendance.scan') }}" class="btn btn-primary">
                                    <i class="fas fa-qrcode me-2"></i>Scan QR Code
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Attendance -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Attendance</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAttendance as $attendance)
                                    <tr>
                                        <td>{{ $attendance->check_in_time ? $attendance->check_in_time->timezone('Asia/Jakarta')->format('d M') : '-' }}</td>
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
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No recent attendance</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Row -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($todayAttendance && !$todayAttendance->check_out_time)
                            <div class="col-md-3 col-6 mb-3">
                                <a href="{{ route('employee.attendance.scan') }}" class="btn btn-success w-100">
                                    <i class="fas fa-sign-out-alt me-2"></i>QR Check-out
                                </a>
                            </div>
                        @else
                            <div class="col-md-3 col-6 mb-3">
                                <a href="{{ route('employee.attendance.scan') }}" class="btn btn-success w-100">
                                    <i class="fas fa-qrcode me-2"></i>QR Check-in
                                </a>
                            </div>
                        @endif
                        <div class="col-md-3 col-6 mb-3">
                            <a href="{{ route('employee.attendance.history') }}" class="btn btn-primary w-100">
                                <i class="fas fa-history me-2"></i>Attendance History
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <a href="{{ route('employee.leave-requests.create') }}" class="btn btn-warning w-100">
                                <i class="fas fa-file-alt me-2"></i>Request Leave
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <a href="{{ route('employee.profile.edit') }}" class="btn btn-secondary w-100">
                                <i class="fas fa-user me-2"></i>Profile Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection