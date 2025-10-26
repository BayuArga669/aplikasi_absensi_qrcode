@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mt-4">QR Code Display</h1>
    <p class="lead">Display this QR code at the office for employee check-in.</p>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Office QR Code</h6>
        </div>
        <div class="card-body text-center">
            <div id="qrCodeContainer" class="d-inline-block border p-3 bg-light rounded mb-3">
                <div id="qrCodeDisplay">
                    <p class="text-muted">Loading QR Code...</p>
                </div>
            </div>
            
            <div class="mt-3">
                <p><strong>Location:</strong> {{ $currentQrCode->office_location }}</p>
                <p><strong>Valid Until:</strong> <span id="countdown">{{ $currentQrCode->valid_until }}</span></p>
            </div>
        </div>
    </div>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        Employees can scan this QR code using the mobile app to check in. 
        The code will automatically refresh when it expires.
    </div>
</div>

<!-- Include QR Code library -->
<script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
<script>
    // Function to generate QR code
    function generateQRCode() {
        // Clear previous QR code if exists
        const container = document.getElementById('qrCodeDisplay');
        container.innerHTML = '';
        
        // Create new QR code
        new QRCode(container, {
            text: "{{ $currentQrCode->code }}",
            width: 200,
            height: 200,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    }
    
    // Generate the QR code when page loads
    document.addEventListener('DOMContentLoaded', function() {
        generateQRCode();
        
        // Set up countdown timer
        const expirationTime = new Date("{{ $currentQrCode->valid_until }}").getTime();
        
        const countdownInterval = setInterval(function() {
            const now = new Date().getTime();
            const distance = expirationTime - now;
            
            if (distance <= 0) {
                clearInterval(countdownInterval);
                document.getElementById('countdown').innerHTML = "EXPIRED";
                
                // Optionally reload the page to get a new QR code
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                document.getElementById('countdown').innerHTML = minutes + "m " + seconds + "s";
            }
        }, 1000);
    });
</script>
@endsection