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
                <!-- Check-in/Check-out Status Card -->
                <div class="col-xl-4 col-lg-5 col-12">
                    <div class="card shadow mb-4">
                        <div class="card-body text-center">
                            @if($todayAttendance)
                                @if($todayAttendance->check_out_time)
                                    <!-- Already checked out -->
                                    <div class="mb-3">
                                        <i class="fas fa-check-circle text-success fa-3x"></i>
                                    </div>
                                    <h4 class="text-success">
                                        <i class="fas fa-check me-1"></i>Already Checked Out
                                    </h4>
                                    <p class="mb-0">Check-in: {{ $todayAttendance->check_in_time->timezone('Asia/Jakarta')->format('d M Y H:i') }}</p>
                                    <p class="mb-0">Check-out: {{ $todayAttendance->check_out_time->timezone('Asia/Jakarta')->format('d M Y H:i') }}</p>
                                    <p class="text-muted">Attendance completed for today</p>
                                @else
                                    <!-- Checked in but not checked out yet -->
                                    <div class="mb-3">
                                        <i class="fas fa-clock text-warning fa-3x"></i>
                                    </div>
                                    <h4 class="text-warning">
                                        <i class="fas fa-clock me-1"></i>Checked In
                                    </h4>
                                    <p class="mb-0">Check-in: {{ $todayAttendance->check_in_time->timezone('Asia/Jakarta')->format('d M Y H:i') }}</p>
                                    @if($officeLocation && $officeLocation->check_out_deadline)
                                        @php
                                            $checkOutDeadline = \Carbon\Carbon::today()->setTime(substr($officeLocation->check_out_deadline, 0, 2), substr($officeLocation->check_out_deadline, 3, 2));
                                            $currentTime = \Carbon\Carbon::now();
                                            $canCheckOut = $currentTime->gte($checkOutDeadline);
                                            $timeRemaining = $currentTime->diff($checkOutDeadline);
                                            $timeRemainingText = $timeRemaining->h . ' hours ' . $timeRemaining->i . ' minutes';
                                        @endphp
                                        @if($canCheckOut)
                                            <p class="text-success mb-0">Check-out time: Anytime after {{ $checkOutDeadline->format('H:i') }}</p>
                                            <p class="text-success">You can check out now</p>
                                        @else
                                            <p class="text-info mb-0">Check-out start time: {{ $checkOutDeadline->format('H:i') }}</p>
                                            <p class="text-warning">Time remaining: {{ $timeRemainingText }}</p>
                                        @endif
                                    @else
                                        <p class="text-muted">Scan QR code to check out</p>
                                    @endif
                                @endif
                            @else
                                <!-- Not checked in yet -->
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
                                    <!-- Check-in scanner -->
                                    <div class="text-center p-5" id="cameraPlaceholder">
                                        <i class="fas fa-camera fa-5x text-muted mb-3"></i>
                                        <p class="text-muted">Camera will activate after permission is granted</p>
                                        <button id="startCamera" class="btn btn-primary">Start Camera</button>
                                    </div>
                                @elseif($todayAttendance && !$todayAttendance->check_out_time)
                                    @if($officeLocation && $officeLocation->check_out_deadline)
                                        @php
                                            $checkOutDeadline = \Carbon\Carbon::today()->setTime(substr($officeLocation->check_out_deadline, 0, 2), substr($officeLocation->check_out_deadline, 3, 2));
                                            $currentTime = \Carbon\Carbon::now();
                                            $canCheckOut = $currentTime->gte($checkOutDeadline);
                                        @endphp
                                        @if(!$canCheckOut)
                                            <!-- Check-out not yet allowed -->
                                            <div class="text-center p-5">
                                                <i class="fas fa-clock fa-5x text-warning mb-3"></i>
                                                <p class="text-warning">Check-out is not allowed before {{ $checkOutDeadline->format('H:i') }}</p>
                                                @php
                                                    $timeRemaining = $currentTime->diff($checkOutDeadline);
                                                    $timeRemainingText = $timeRemaining->h . ' hours ' . $timeRemaining->i . ' minutes';
                                                @endphp
                                                <p class="text-info">Time remaining: {{ $timeRemainingText }}</p>
                                            </div>
                                        @else
                                            <!-- Check-out scanner -->
                                            <div class="text-center p-5" id="cameraPlaceholder">
                                                <i class="fas fa-camera fa-5x text-muted mb-3"></i>
                                                <p class="text-muted">Camera will activate after permission is granted</p>
                                                <button id="startCamera" class="btn btn-primary">Start Camera</button>
                                            </div>
                                        @endif
                                    @else
                                        <!-- Check-out scanner -->
                                        <div class="text-center p-5" id="cameraPlaceholder">
                                            <i class="fas fa-camera fa-5x text-muted mb-3"></i>
                                            <p class="text-muted">Camera will activate after permission is granted</p>
                                            <button id="startCamera" class="btn btn-primary">Start Camera</button>
                                        </div>
                                    @endif
                                @else
                                    <!-- Already checked out -->
                                    <div class="text-center p-5">
                                        <i class="fas fa-check-circle fa-5x text-muted mb-3"></i>
                                        <p class="text-muted">Attendance completed for today</p>
                                    </div>
                                @endif
                            </div>
                            
                            @if(!$todayAttendance)
                                <!-- Check-in form -->
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
                            @elseif($todayAttendance && !$todayAttendance->check_out_time)
                                @if($officeLocation && $officeLocation->check_out_deadline)
                                    @php
                                        $checkOutDeadline = \Carbon\Carbon::today()->setTime(substr($officeLocation->check_out_deadline, 0, 2), substr($officeLocation->check_out_deadline, 3, 2));
                                        $currentTime = \Carbon\Carbon::now();
                                        $canCheckOut = $currentTime->gte($checkOutDeadline);
                                    @endphp
                                    @if(!$canCheckOut)
                                        <!-- Check-out not yet allowed -->
                                        <div class="mt-3">
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                Check-out is not allowed before {{ $checkOutDeadline->format('H:i') }}.
                                                Time remaining: {{ $currentTime->diff($checkOutDeadline)->h }} hours {{ $currentTime->diff($checkOutDeadline)->i }} minutes.
                                            </div>
                                        </div>
                                    @else
                                        <!-- Check-out form -->
                                        <div class="mt-3">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Make sure you are within the office area to ensure location validation passes.
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="manualCheckOut">Or enter the QR code manually:</label>
                                                <input type="text" class="form-control" id="manualCheckOut" placeholder="Enter QR code value">
                                            </div>

                                            <button id="manualCheckOutBtn" class="btn btn-success w-100" disabled>
                                                <i class="fas fa-sign-out-alt me-2"></i>Check Out Manually
                                            </button>
                                        </div>
                                    @endif
                                @else
                                    <!-- Check-out form -->
                                    <div class="mt-3">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Make sure you are within the office area to ensure location validation passes.
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="manualCheckOut">Or enter the QR code manually:</label>
                                            <input type="text" class="form-control" id="manualCheckOut" placeholder="Enter QR code value">
                                        </div>

                                        <button id="manualCheckOutBtn" class="btn btn-success w-100" disabled>
                                            <i class="fas fa-sign-out-alt me-2"></i>Check Out Manually
                                        </button>
                                    </div>
                                @endif
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
                    
                    // If we're within office, we can enable check-in or check-out
                    const hasAttendance = @json($todayAttendance ? true : false);
                    const todayAttendance = @json($todayAttendance);
                    const canCheckOut = todayAttendance && !todayAttendance.check_out_time;
                    
                    if (distance <= officeRadius) {
                        if (!hasAttendance) {
                            document.getElementById('manualCheckInBtn').disabled = false;
                        } else if (canCheckOut) {
                            document.getElementById('manualCheckOutBtn').disabled = false;
                        }
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
                            const todayAttendance = @json($todayAttendance);
                            
                            if (!todayAttendance) {
                                // Check-in mode
                                document.getElementById('manualCheckIn').value = code.data;
                                document.getElementById('result').innerHTML = `<span class="text-success">QR Code detected: ${code.data.substring(0, 20)}...</span>`;
                                
                                // Process check-in if location is valid
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
                                            document.getElementById('result').innerHTML = '<span class="text-success">' + data.message + '!</span>';
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
                            } else if (todayAttendance && !todayAttendance.check_out_time) {
                                // Check if user can check out based on office deadline
                                @if($officeLocation && $officeLocation->check_out_deadline)
                                    @php
                                        $checkOutDeadline = \Carbon\Carbon::today()->setTime(substr($officeLocation->check_out_deadline, 0, 2), substr($officeLocation->check_out_deadline, 3, 2));
                                        $currentTime = \Carbon\Carbon::now();
                                        $canCheckOut = $currentTime->gte($checkOutDeadline);
                                    @endphp
                                    @if(!$canCheckOut)
                                        document.getElementById('result').innerHTML = `<span class="text-warning">Check-out is not allowed before {{ $checkOutDeadline->format('H:i') }}. Time remaining: {{ $currentTime->diff($checkOutDeadline)->h }} hours {{ $currentTime->diff($checkOutDeadline)->i }} minutes.</span>`;
                                        return; // Exit the function early
                                    @endif
                                @endif

                                // Check-out mode
                                document.getElementById('manualCheckOut').value = code.data;
                                document.getElementById('result').innerHTML = `<span class="text-success">QR Code detected: ${code.data.substring(0, 20)}...</span>`;

                                // Process check-out if location is valid
                                if (currentLocation) {
                                    fetch('/employee/attendance/check-out', {
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
                                            document.getElementById('result').innerHTML = '<span class="text-success">' + data.message + '!</span>';
                                            setTimeout(() => location.reload(), 1500);
                                        } else {
                                            document.getElementById('result').innerHTML = `<span class="text-danger">Check-out failed: ${data.message}</span>`;
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        document.getElementById('result').innerHTML = '<span class="text-danger">An error occurred during check-out</span>';
                                    });
                                }
                            } else {
                                // Already checked out
                                document.getElementById('result').innerHTML = '<span class="text-info">Attendance completed for today</span>';
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
            const hasAttendance = @json($todayAttendance ? true : false);
            
            if (qrValue.length > 0 && !hasAttendance) {
                checkInBtn.disabled = false;
            } else {
                checkInBtn.disabled = true;
            }
        });
        
        document.getElementById('manualCheckInBtn').addEventListener('click', function() {
            const qrValue = document.getElementById('manualCheckIn').value.trim();
            if (qrValue) {
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
                        alert(data.message);
                        location.reload();
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
        
        // Manual check-out handling
        document.getElementById('manualCheckOut').addEventListener('input', function() {
            const qrValue = this.value.trim();
            const checkOutBtn = document.getElementById('manualCheckOutBtn');
            const todayAttendance = @json($todayAttendance);
            const canCheckOut = todayAttendance && !todayAttendance.check_out_time;
            
            if (qrValue.length > 0 && canCheckOut) {
                checkOutBtn.disabled = false;
            } else {
                checkOutBtn.disabled = true;
            }
        });
        
        document.getElementById('manualCheckOutBtn').addEventListener('click', function() {
            // Check if user can check out based on office deadline
            @if($officeLocation && $officeLocation->check_out_deadline)
                @php
                    $checkOutDeadline = \Carbon\Carbon::today()->setTime(substr($officeLocation->check_out_deadline, 0, 2), substr($officeLocation->check_out_deadline, 3, 2));
                    $currentTime = \Carbon\Carbon::now();
                    $canCheckOut = $currentTime->gte($checkOutDeadline);
                @endphp
                @if(!$canCheckOut)
                    alert('Check-out is not allowed before {{ $checkOutDeadline->format('H:i') }}. Time remaining: {{ $currentTime->diff($checkOutDeadline)->h }} hours {{ $currentTime->diff($checkOutDeadline)->i }} minutes.');
                    return; // Exit the function early
                @endif
            @endif

            const qrValue = document.getElementById('manualCheckOut').value.trim();
            if (qrValue) {
                fetch('/employee/attendance/check-out', {
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
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Check-out failed: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred during check-out');
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