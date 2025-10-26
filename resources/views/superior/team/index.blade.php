@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mt-4">Team Monitoring</h1>
    <p class="lead">Monitor your team members' attendance and performance.</p>
    
    <!-- Search and Filter -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" class="form-control" placeholder="Search team members..." id="searchInput">
                </div>
                <div class="col-md-6">
                    <select class="form-control" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="present">Present</option>
                        <option value="late">Late</option>
                        <option value="absent">Absent</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Team Members -->
    <div class="row" id="teamMembers">
        @forelse($teamMembers as $member)
            <div class="col-xl-4 col-md-6 mb-4 team-member" data-status="{{ $member->attendance_status }}" data-name="{{ strtolower($member->name) }}">
                <div class="card border-left-{{ $member->attendance_status === 'present' ? 'success' : ($member->attendance_status === 'late' ? 'warning' : 'danger') }} shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">
                                    {{ $member->name }}
                                </div>
                                <div class="h6 mb-0 text-gray-800">{{ $member->position }}</div>
                                <div class="mt-2">
                                    <span class="attendance-status 
                                        @if($member->attendance_status === 'present') status-present
                                        @elseif($member->attendance_status === 'late') status-late
                                        @else status-absent @endif">
                                        <i class="fas fa-{{ $member->attendance_status === 'present' ? 'check-circle' : ($member->attendance_status === 'late' ? 'clock' : 'times-circle') }} me-1"></i>
                                        {{ ucfirst($member->attendance_status ?? 'absent') }}
                                    </span>
                                </div>
                                @if($member->check_in_time)
                                    <div class="text-xs text-muted mt-1">
                                        Check-in: {{ $member->check_in_time }}
                                    </div>
                                @endif
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user fa-2x text-gray-300"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('superior.team.show', $member->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye me-1"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-muted text-center">No team members found.</p>
            </div>
        @endforelse
    </div>
    
    <!-- Pagination -->
    @if($teamMembers->hasPages())
        <div class="d-flex justify-content-center">
            {{ $teamMembers->links() }}
        </div>
    @endif
</div>

<script>
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const members = document.querySelectorAll('.team-member');
        
        members.forEach(function(member) {
            const name = member.getAttribute('data-name');
            if (name.includes(searchTerm)) {
                member.style.display = 'block';
            } else {
                member.style.display = 'none';
            }
        });
    });
    
    document.getElementById('statusFilter').addEventListener('change', function() {
        const status = this.value;
        const members = document.querySelectorAll('.team-member');
        
        members.forEach(function(member) {
            const memberStatus = member.getAttribute('data-status');
            if (status === '' || memberStatus === status) {
                member.style.display = 'block';
            } else {
                member.style.display = 'none';
            }
        });
    });
</script>
@endsection