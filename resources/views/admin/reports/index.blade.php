@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4">Attendance Reports</h1>
    <p class="lead">Generate and view attendance reports for employees.</p>
    
    <!-- Report Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Report Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.index') }}">
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
                        <label for="employee_id" class="form-label">Employee</label>
                        <select class="form-control" id="employee_id" name="employee_id">
                            <option value="">All Employees</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <label for="department" class="form-label">Department</label>
                        <input type="text" class="form-control" id="department" name="department" value="{{ request('department') }}" placeholder="Search by department">
                    </div>
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="on_time" {{ request('status') == 'on_time' ? 'selected' : '' }}>On Time</option>
                            <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                            <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">Reset</a>
                            <button type="button" class="btn btn-success" onclick="exportReport()">
                                <i class="fas fa-file-export me-2"></i>Export
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Report Summary -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Attendance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalAttendance ?? 0 }}</div>
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
                                Present</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $presentCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                Late</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $lateCount ?? 0 }}</div>
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
                                Absent</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $absentCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Attendance Details -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Attendance Details</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Status</th>
                            <th>Location</th>
                            <th>Distance (m)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendanceRecords as $attendance)
                            <tr>
                                <td>{{ $attendance->user->name ?? 'N/A' }}</td>
                                <td>{{ $attendance->check_in_time ? $attendance->check_in_time->timezone('Asia/Jakarta')->format('d M Y') : '-' }}</td>
                                <td>{{ $attendance->check_in_time ? $attendance->check_in_time->timezone('Asia/Jakarta')->format('H:i') : '-' }}</td>
                                <td>{{ $attendance->check_out_time ?? '-' }}</td>
                                <td>
                                    <span class="attendance-status 
                                        @if($attendance->status === 'on_time') status-present
                                        @elseif($attendance->status === 'late') status-late
                                        @else status-absent @endif">
                                        @if($attendance->status === 'on_time')
                                            <i class="fas fa-check text-success me-1"></i>On Time
                                        @elseif($attendance->status === 'late')
                                            <i class="fas fa-clock text-warning me-1"></i>Late
                                        @else
                                            <i class="fas fa-times text-danger me-1"></i>{{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                                        @endif
                                    </span>
                                </td>
                                <td>{{ $attendance->check_in_latitude ?? 'N/A' }}, {{ $attendance->check_in_longitude ?? 'N/A' }}</td>
                                <td>{{ $attendance->qrCode && $attendance->qrCode->officeLocation ? $attendance->qrCode->officeLocation->radius . 'm' : 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No attendance records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                
                @if($attendanceRecords->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $attendanceRecords->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function exportReport() {
        // In a real implementation, this would send the form data to an export endpoint
        alert('Export functionality would be implemented here to generate Excel/PDF reports');
    }
</script>
@endsection