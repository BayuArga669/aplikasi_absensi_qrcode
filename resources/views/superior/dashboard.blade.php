@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Superior Dashboard</h1>
        <a href="{{ route('superior.late-reports') }}" class="d-none d-sm-inline-block btn btn-sm btn-warning shadow-sm">
            <i class="fas fa-clock fa-sm text-white-50"></i> Late Reports
        </a>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Team Size Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Team Size</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $teamSize ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Present Today Card -->
        <div class="col-xl-3 col-md-6 mb-4">
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
        <div class="col-xl-3 col-md-6 mb-4">
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
        <div class="col-xl-3 col-md-6 mb-4">
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
        <!-- Recent Late Arrivals -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-warning">Recent Late Arrivals</h6>
                </div>
                <div class="card-body">
                    @if($recentLateArrivals->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Check-in Time</th>
                                        <th>Late By</th>
                                        <th>Date</th>
                                        <th>Location</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentLateArrivals as $late)
                                        <tr>
                                            <td>{{ $late->user->name ?? 'N/A' }}</td>
                                            <td>{{ $late->check_in_time }}</td>
                                            <td>
                                                <span class="badge bg-warning">
                                                    {{ $late->late_duration ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>{{ $late->date->format('Y-m-d') }}</td>
                                            <td>{{ $late->location ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">No late arrivals today.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Team Attendance Overview -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <!-- Card Header -->
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Team Attendance Chart</h6>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="teamAttendanceChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Present
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-warning"></i> Late
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-danger"></i> Absent
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Team Attendance Overview</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Position</th>
                                    <th>Status Today</th>
                                    <th>Check-in Time</th>
                                    <th>Check-out Time</th>
                                    <th>Attendance Rate (This Month)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($teamAttendance as $member)
                                    <tr>
                                        <td>{{ $member->name ?? 'N/A' }}</td>
                                        <td>{{ $member->position ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge 
                                                @if($member->attendance_status === 'present') bg-success
                                                @elseif($member->attendance_status === 'late') bg-warning
                                                @else bg-danger @endif">
                                                {{ ucfirst($member->attendance_status ?? 'absent') }}
                                            </span>
                                        </td>
                                        <td>{{ $member->check_in_time ?? '-' }}</td>
                                        <td>{{ $member->check_out_time ?? '-' }}</td>
                                        <td>
                                            @if($member->attendance_rate)
                                                {{ number_format($member->attendance_rate, 1) }}%
                                            @else
                                                0%
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No team members found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Set new default font family and font color to mimic Bootstrap's default styling
    Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
    Chart.defaults.global.defaultFontColor = '#858796';

    // Team Attendance Chart
    var ctx = document.getElementById("teamAttendanceChart");
    var myPieChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ["Present", "Late", "Absent"],
            datasets: [{
                data: [{{ $presentToday ?? 0 }}, {{ $lateToday ?? 0 }}, {{ $absentToday ?? 0 }}],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                hoverBackgroundColor: ['#218838', '#e0a800', '#c82333'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            maintainAspectRatio: false,
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                caretPadding: 10,
            },
            legend: {
                display: false
            },
            cutoutPercentage: 80,
        },
    });
</script>
@endsection