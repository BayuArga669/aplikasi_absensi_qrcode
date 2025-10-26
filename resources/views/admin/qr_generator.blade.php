@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mt-4">QR Code Generator</h1>
    <p class="lead">Generate dynamic QR codes for attendance check-in.</p>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">QR Code Generator</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="duration" class="form-label">QR Code Duration (seconds)</label>
                        <input type="number" class="form-control" id="duration" value="30" min="10" max="300">
                        <div class="form-text">QR code will expire after this duration and regenerate</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="office_location" class="form-label">Office Location</label>
                        <input type="text" class="form-control" id="office_location" value="Office Main Entrance" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="coordinates" class="form-label">Office Coordinates</label>
                        <input type="text" class="form-control" id="coordinates" value="-6.200000, 106.816666" readonly>
                    </div>
                    
                    <button id="generateBtn" class="btn btn-primary">
                        <i class="fas fa-sync-alt me-2"></i>Generate QR Code
                    </button>
                </div>
                
                <div class="col-md-6">
                    <div class="text-center">
                        <div id="qrContainer">
                            <div id="qrCode" class="border p-3 bg-light rounded">
                                <p class="text-muted">Click "Generate QR Code" to create a new QR code</p>
                            </div>
                        </div>
                        <div id="countdown" class="mt-3">
                            <span class="text-muted">QR Code will expire in: <span id="timeLeft">30</span> seconds</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- How to Use Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">How to Use</h6>
        </div>
        <div class="card-body">
            <ol>
                <li>Set the duration for how long the QR code should be valid</li>
                <li>Click "Generate QR Code" to create a new QR code</li>
                <li>Display the QR code on a screen visible to employees at the office</li>
                <li>Employees can scan this QR code for attendance check-in</li>
                <li>The QR code will automatically regenerate after the set duration</li>
            </ol>
            
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle me-2"></i>
                The QR code contains encrypted information that validates both time and location for attendance.
            </div>
        </div>
    </div>
</div>

<!-- QR Code Library -->
<script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
<script>
    let qrCodeInstance = null;
    let countdownInterval = null;
    let timeLeft = 30;
    
    document.getElementById('generateBtn').addEventListener('click', generateQRCode);
    
    function generateQRCode() {
        // Clear previous QR code
        if (qrCodeInstance) {
            qrCodeInstance.clear();
            document.getElementById('qrCode').innerHTML = '';
        }
        
        // Generate new QR data (this would come from backend in real implementation)
        const qrData = `QRABSENSI:${Date.now()}:${Math.random().toString(36).substring(2, 15)}`;
        
        // Create new QR code
        qrCodeInstance = new QRCode(document.getElementById('qrCode'), {
            text: qrData,
            width: 200,
            height: 200,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
        
        // Reset and start countdown
        clearInterval(countdownInterval);
        timeLeft = parseInt(document.getElementById('duration').value) || 30;
        document.getElementById('timeLeft').textContent = timeLeft;
        
        countdownInterval = setInterval(() => {
            timeLeft--;
            document.getElementById('timeLeft').textContent = timeLeft;
            
            if (timeLeft <= 0) {
                clearInterval(countdownInterval);
                generateQRCode(); // Auto-generate new QR code
            }
        }, 1000);
    }
    
    // Generate initial QR code on load
    window.onload = function() {
        generateQRCode();
    };
</script>
@endsection