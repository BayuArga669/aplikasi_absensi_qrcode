@extends('layouts.app')

@section('content')
<div class="container-fluid vh-100 d-flex flex-column">
    <div class="text-center py-3 d-flex justify-content-between align-items-center px-4">
        <div>
            <h1 class="display-4 text-primary">QR Code Attendance</h1>
            <p class="lead">Display this QR code for employee check-in</p>
        </div>
        <div class="dropdown">
            <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                <li>
                    <a class="dropdown-item" href="{{ route('admin.qr-generator') }}">
                        <i class="fas fa-sync-alt me-2"></i>QR Generator
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('admin.qr-code.display', $officeLocation->id) }}">
                        <i class="fas fa-qrcode me-2"></i>Regular QR Display
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="#" id="openPopupBtn">
                        <i class="fas fa-external-link-alt me-2"></i>Open in New Window
                    </a>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="flex-grow-1 d-flex align-items-center justify-content-center">
        <div class="text-center">
            <div id="qrCodeContainer" class="d-inline-block border p-4 bg-light rounded shadow-lg">
                <div id="qrCodeDisplay" class="d-flex align-items-center justify-content-center" style="min-height: 400px; min-width: 400px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <h4 class="text-primary">{{ $officeLocation->name }}</h4>
                <p class="text-muted">{{ $officeLocation->address }}</p>
                <p><strong>QR Code Valid Until:</strong> <span id="countdown">{{ $currentQrCode->expires_at }}</span></p>
            </div>
        </div>
    </div>
    
    <div class="text-center py-3 bg-light border-top">
        <p class="mb-0 text-muted">Employees can scan this QR code using the mobile app to check in</p>
    </div>
</div>

<!-- Include QR Code library -->
<script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
<script>
    let qrPopupWindow = null;
    const qrCodeData = "{{ $currentQrCode->code }}";
    const officeName = "{{ addslashes($officeLocation->name) }}";
    const officeAddress = "{{ addslashes($officeLocation->address) }}";
    const expirationTime = new Date("{{ $currentQrCode->expires_at }}").getTime();
    
    // Function to generate large QR code
    function generateQRCode() {
        const container = document.getElementById('qrCodeDisplay');
        if (!container) return;
        
        container.innerHTML = '';
        
        try {
            new QRCode(container, {
                text: qrCodeData,
                width: 350,
                height: 350,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        } catch(e) {
            console.error('QR Code generation error:', e);
            container.innerHTML = '<p class="text-danger">Error generating QR code</p>';
        }
    }
    
    // Open popup window
    document.getElementById('openPopupBtn').addEventListener('click', function(e) {
        e.preventDefault();
        
        // Check if popup is already open
        if (qrPopupWindow && !qrPopupWindow.closed) {
            qrPopupWindow.focus();
            return;
        }
        
        // Open centered popup window
        const width = 800;
        const height = 900;
        const left = (screen.width - width) / 2;
        const top = (screen.height - height) / 2;
        
        qrPopupWindow = window.open(
            '',
            'QRCodeDisplay',
            'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top + ',toolbar=no,menubar=no,scrollbars=no,resizable=yes'
        );
        
        if (qrPopupWindow) {
            createPopupContent();
        } else {
            alert('Please allow popups for this site');
        }
    });
    
    function createPopupContent() {
        const doc = qrPopupWindow.document;
        
        const htmlContent = '<!DOCTYPE html>' +
            '<html>' +
            '<head>' +
            '<meta charset="utf-8">' +
            '<meta name="viewport" content="width=device-width, initial-scale=1">' +
            '<title>QR Code Display - ' + officeName + '</title>' +
            '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">' +
            '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">' +
            '<style>' +
            'body {' +
            '    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);' +
            '    min-height: 100vh;' +
            '    display: flex;' +
            '    flex-direction: column;' +
            '    align-items: center;' +
            '    justify-content: center;' +
            '    margin: 0;' +
            '    padding: 20px;' +
            '    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;' +
            '}' +
            '.qr-container { text-align: center; color: white; }' +
            '.qr-title { font-size: 2rem; font-weight: bold; margin-bottom: 1rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.2); }' +
            '.qr-subtitle { font-size: 1.8rem; margin-bottom: 0.5rem; font-weight: 600; }' +
            '.qr-address { font-size: 1.2rem; opacity: 0.95; margin-bottom: 3rem; }' +
            '.qr-box {' +
            '    background: white;' +
            '    padding: 40px;' +
            '    border-radius: 20px;' +
            '    box-shadow: 0 25px 70px rgba(0, 0, 0, 0.4);' +
            '    display: inline-block;' +
            '    margin-bottom: 2rem;' +
            '}' +
            '.countdown-badge {' +
            '    background: white;' +
            '    color: #667eea;' +
            '    padding: 18px 45px;' +
            '    border-radius: 50px;' +
            '    font-size: 1.6rem;' +
            '    font-weight: bold;' +
            '    box-shadow: 0 10px 35px rgba(0, 0, 0, 0.3);' +
            '}' +
            '.footer-text { margin-top: 2.5rem; font-size: 1.3rem; opacity: 0.95; }' +
            '.expired { color: #ff6b6b !important; }' +
            '</style>' +
            '</head>' +
            '<body>' +
            '<div class="qr-container">' +
            '<div class="qr-title"><i class="fas fa-qrcode me-3"></i>Scan to Check In</div>' +
            '<div class="qr-subtitle">' + officeName + '</div>' +
            '<div class="qr-address">' + officeAddress + '</div>' +
            '<div class="qr-box"><div id="qrCodeDisplay"></div></div>' +
            '<div class="countdown-badge">' +
            '<i class="fas fa-clock me-2"></i>' +
            '<span id="countdownText">Expires in: <span id="timeLeft">--</span></span>' +
            '</div>' +
            '<div class="footer-text">' +
            '<i class="fas fa-mobile-alt me-2"></i>' +
            'Open your attendance app and scan this QR code' +
            '</div>' +
            '</div>' +
            '<script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"><\/script>' +
            '<script>' +
            'window.onload = function() {' +
            '    try {' +
            '        new QRCode(document.getElementById("qrCodeDisplay"), {' +
            '            text: "' + qrCodeData + '",' +
            '            width: 500,' +
            '            height: 500,' +
            '            colorDark: "#000000",' +
            '            colorLight: "#ffffff",' +
            '            correctLevel: QRCode.CorrectLevel.H' +
            '        });' +
            '    } catch(e) {' +
            '        console.error("QR Code Error:", e);' +
            '    }' +
            '    var expirationTime = ' + expirationTime + ';' +
            '    var countdownInterval = setInterval(function() {' +
            '        var now = new Date().getTime();' +
            '        var distance = expirationTime - now;' +
            '        if (distance <= 0) {' +
            '            clearInterval(countdownInterval);' +
            '            document.getElementById("countdownText").innerHTML = "<span class=\\"expired\\">EXPIRED - Refreshing...</span>";' +
            '            setTimeout(function() { window.location.reload(); }, 2000);' +
            '        } else {' +
            '            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));' +
            '            var seconds = Math.floor((distance % (1000 * 60)) / 1000);' +
            '            document.getElementById("timeLeft").textContent = minutes + "m " + seconds + "s";' +
            '        }' +
            '    }, 1000);' +
            '};' +
            '<\/script>' +
            '</body>' +
            '</html>';
        
        doc.open();
        doc.write(htmlContent);
        doc.close();
    }
    
    // Generate the QR code when page loads
    document.addEventListener('DOMContentLoaded', function() {
        generateQRCode();
        
        // Set up countdown timer for main page
        const countdownInterval = setInterval(function() {
            const now = new Date().getTime();
            const distance = expirationTime - now;
            
            if (distance <= 0) {
                clearInterval(countdownInterval);
                document.getElementById('countdown').innerHTML = "EXPIRED";
                
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