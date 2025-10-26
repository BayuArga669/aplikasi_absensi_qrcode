@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">QR Code Check-in</h1>
        <a href="{{ route('employee.attendance.history') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-history fa-sm text-white-50"></i> Attendance History
        </a>
    </div>

    <div class="row flex-column flex-md-row">
        <div class="col-lg-12">
            <div class="row flex-column flex-md-row">
                <!-- Check-in Status Card -->
                <div class="col-xl-4 col-lg-5 col-12">
                    <div class="card shadow mb-4">
                        <div class="card-body text-center">
                            @if($todayAttendance)
                                <div class="mb-3">
                                    <i class="fas fa-{{ $todayAttendance->status === 'present' ? 'check-circle text-success' : ($todayAttendance->status === 'late' ? 'clock text-warning' : 'times-circle text-danger') }} fa-3x"></i>
                                </div>
                                <h4 class="text-{{ $todayAttendance->status === 'present' ? 'success' : ($todayAttendance->status === 'late' ? 'warning' : 'danger') }}">
                                    Already Checked In
                                </h4>
                                <p class="mb-0">Check-in time: {{ $todayAttendance->check_in_time }}</p>
                                <p class="text-muted">You can only check in once per day</p>
                            @else
                                <div class="mb-3">
                                    <i class="fas fa-qrcode fa-3x text-primary"></i>
                                </div>
                                <h4>Ready to Check In</h4>
                                <p class="text-muted">Point your camera at the QR code at the office to scan</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- QR Scanner Card -->
                <div class="col-xl-8 col-lg-7 col-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">QR Scanner</h6>
                        </div>
                        <div class="card-body">
                            <div id="reader" class="border rounded overflow-hidden">
                                @if(!$todayAttendance)
                                    <div class="text-center p-5" id="cameraPlaceholder">
                                        <i class="fas fa-camera fa-5x text-muted mb-3"></i>
                                        <p class="text-muted">Camera will activate after permission is granted</p>
                                        <button id="startCamera" class="btn btn-primary">Start Camera</button>
                                    </div>
                                @else
                                    <div class="text-center p-5">
                                        <i class="fas fa-ban fa-5x text-muted mb-3"></i>
                                        <p class="text-muted">Check-in already completed for today</p>
                                    </div>
                                @endif
                            </div>
                            
                            @if(!$todayAttendance)
                                <div class="mt-3">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Make sure you are within the office area to ensure location validation passes.
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="manualCheckIn">Or enter the QR code manually:</label>
                                        <input type="text" class="form-control" id="manualCheckIn" placeholder="Enter QR code value">
                                    </div>
                                    
                                    <button id="manualCheckInBtn" class="btn btn-primary w-100" disabled>
                                        <i class="fas fa-check me-2"></i>Check In Manually
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location Info Card -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Location Information</h6>
                </div>
                <div class="card-body">
                    <div class="row flex-column flex-md-row">
                        <div class="col-md-8 col-12 mb-3 mb-md-0">
                            <h6 class="font-weight-bold">Current Location</h6>
                            <p class="mb-0" id="currentLocation">Detecting location...</p>
                            <small class="text-muted" id="locationAccuracy">Accuracy: </small>
                        </div>
                        <div class="col-md-4 col-12">
                            <h6 class="font-weight-bold">Status</h6>
                            <span id="locationStatus" class="badge bg-secondary">Checking...</span>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="progress" style="height: 20px;">
                                <div id="locationProgress" class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">Distance: -- m</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include jsQR for QR code scanning -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>

<script>
    let currentLocation = null;
    @if(isset($officeLocation))
    let officeLocation = { lat: {{ $officeLocation->latitude }}, lng: {{ $officeLocation->longitude }} }; // Office location from database
    let officeRadius = {{ $officeLocation->radius }}; // Office radius from database
    @else
    let officeLocation = { lat: -6.200000, lng: 106.816666 }; // Default fallback location
    let officeRadius = 50; // 50 meters fallback radius
    @endif
    let locationAccuracy = 0;
    
    // Get user's location
    function getCurrentLocation() {
        const locationStatus = document.getElementById('locationStatus');
        const currentLocationEl = document.getElementById('currentLocation');
        const locationAccuracyEl = document.getElementById('locationAccuracy');
        const locationProgress = document.getElementById('locationProgress');
        
        if (navigator.geolocation) {
            locationStatus.className = 'badge bg-warning';
            locationStatus.textContent = 'Getting location...';
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    currentLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    locationAccuracy = position.coords.accuracy;
                    
                    // Update UI
                    currentLocationEl.textContent = `${currentLocation.lat.toFixed(6)}, ${currentLocation.lng.toFixed(6)}`;
                    locationAccuracyEl.textContent = `Accuracy: ${locationAccuracy.toFixed(2)} meters`;
                    
                    // Calculate distance from office
                    const distance = calculateDistance(currentLocation, officeLocation);
                    
                    // Update progress bar
                    const percentage = Math.min(100, (distance / officeRadius) * 100);
                    locationProgress.style.width = `${percentage}%`;
                    locationProgress.textContent = `Distance: ${distance.toFixed(2)} m`;
                    locationProgress.className = percentage <= 100 ? 'progress-bar' : 'progress-bar';
                    
                    // Update status based on distance
                    if (distance <= officeRadius) {
                        locationStatus.className = 'badge bg-success';
                        locationStatus.textContent = 'Within office area';
                        locationProgress.className = 'progress-bar bg-success';
                    } else {
                        locationStatus.className = 'badge bg-danger';
                        locationStatus.textContent = 'Outside office area';
                        locationProgress.className = 'progress-bar bg-danger';
                    }
                    
                    // If we're within office, we can enable check-in
                    if (distance <= officeRadius && !{{ $todayAttendance ? 'true' : 'false' }}) {
                        document.getElementById('manualCheckInBtn').disabled = false;
                    }
                },
                function(error) {
                    console.error('Error getting location:', error);
                    locationStatus.className = 'badge bg-danger';
                    locationStatus.textContent = 'Location access denied';
                    currentLocationEl.textContent = 'Unable to get location';
                    locationAccuracyEl.textContent = 'Please enable location services';
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 60000
                }
            );
        } else {
            locationStatus.className = 'badge bg-danger';
            locationStatus.textContent = 'Geolocation not supported';
            currentLocationEl.textContent = 'Geolocation not supported';
            locationAccuracyEl.textContent = 'Please use a modern browser';
        }
    }
    
    // Calculate distance between two points (in meters)
    function calculateDistance(point1, point2) {
        const R = 6371e3; // Earth's radius in meters
        const φ1 = point1.lat * Math.PI/180;
        const φ2 = point2.lat * Math.PI/180;
        const Δφ = (point2.lat-point1.lat) * Math.PI/180;
        const Δλ = (point2.lng-point1.lng) * Math.PI/180;
        
        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                Math.cos(φ1) * Math.cos(φ2) *
                Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        
        return R * c; // Distance in meters
    }
    
    // Start camera for QR scanning
    function startCamera() {
        document.getElementById('startCamera').style.display = 'none';
        document.getElementById('cameraPlaceholder').innerHTML = `
            <video id="video" width="100%" height="auto" autoplay playsinline></video>
            <canvas id="canvas" style="display: none;"></canvas>
            <div id="result" class="mt-3 text-center" style="min-height: 30px;"></div>
        `;
        
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const canvasContext = canvas.getContext('2d');
        let scanningActive = true;
        
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
                .then(function(stream) {
                    video.srcObject = stream;
                    
                    // Function to scan QR codes
                    function scanQR() {
                        if (!scanningActive) return;
                        
                        // Set canvas dimensions to match video
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        
                        // Draw video frame to canvas
                        canvasContext.drawImage(video, 0, 0, canvas.width, canvas.height);
                        
                        // Get image data from canvas
                        const imageData = canvasContext.getImageData(0, 0, canvas.width, canvas.height);
                        
                        // Decode QR code
                        const code = jsQR(imageData.data, imageData.width, imageData.height);
                        
                        if (code) {
                            // QR code detected
                            document.getElementById('manualCheckIn').value = code.data;
                            document.getElementById('result').innerHTML = `<span class="text-success">QR Code detected: ${code.data.substring(0, 20)}...</span>`;
                            
                            // Automatically process check-in if location is valid
                            if (currentLocation) {
                                fetch('/employee/attendance/check-in', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    },
                                    body: JSON.stringify({
                                        qr_code: code.data,
                                        latitude: currentLocation.lat,
                                        longitude: currentLocation.lng
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        document.getElementById('result').innerHTML = '<span class="text-success">Check-in successful!</span>';
                                        setTimeout(() => location.reload(), 1500);
                                    } else {
                                        document.getElementById('result').innerHTML = `<span class="text-danger">Check-in failed: ${data.message}</span>`;
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    document.getElementById('result').innerHTML = '<span class="text-danger">An error occurred during check-in</span>';
                                });
                            }
                        }
                        
                        // Continue scanning
                        requestAnimationFrame(scanQR);
                    }
                    
                    // Start scanning after video loads
                    video.addEventListener('play', function() {
                        requestAnimationFrame(scanQR);
                    });
                })
                .catch(function(err) {
                    console.error("An error occurred: ", err);
                    document.getElementById('cameraPlaceholder').innerHTML = `
                        <p class="text-danger">Could not access the camera. Please check permissions.</p>
                        <button id="retryCamera" class="btn btn-secondary">Retry</button>
                    `;
                    document.getElementById('retryCamera').addEventListener('click', startCamera);
                });
        } else {
            document.getElementById('cameraPlaceholder').innerHTML = `
                <p class="text-danger">Camera not supported in this browser.</p>
                <p class="text-muted">Please use a modern browser with camera support.</p>
            `;
        }
    }
    
    // Initialize location services when page loads
    document.addEventListener('DOMContentLoaded', function() {
        getCurrentLocation();
        
        // Refresh location every 30 seconds
        setInterval(getCurrentLocation, 30000);
        
        // Start camera button
        document.getElementById('startCamera').addEventListener('click', startCamera);
        
        // Manual check-in handling
        document.getElementById('manualCheckIn').addEventListener('input', function() {
            const qrValue = this.value.trim();
            const checkInBtn = document.getElementById('manualCheckInBtn');
            
            if (qrValue.length > 0 && !{{ $todayAttendance ? 'true' : 'false' }}) {
                checkInBtn.disabled = false;
            } else {
                checkInBtn.disabled = true;
            }
        });
        
        document.getElementById('manualCheckInBtn').addEventListener('click', function() {
            const qrValue = document.getElementById('manualCheckIn').value.trim();
            if (qrValue) {
                // In a real implementation, this would make an API call to record attendance
                // Send QR code and location to server
                fetch('/employee/attendance/check-in', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        qr_code: qrValue,
                        latitude: currentLocation.lat,
                        longitude: currentLocation.lng
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Check-in successful!');
                        location.reload(); // Refresh the page to show the updated status
                    } else {
                        alert('Check-in failed: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred during check-in');
                });
            }
        });
    });
    
    // Refresh location button functionality
    function refreshLocation() {
        getCurrentLocation();
    }
</script>
@endsection