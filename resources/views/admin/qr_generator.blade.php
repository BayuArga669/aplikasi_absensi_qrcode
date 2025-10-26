@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mt-4">QR Code Generator</h1>
    <p class="lead">Generate dynamic QR codes for attendance check-in.</p>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">QR Code Generator</h6>
            <div class="dropdown">
                <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.offices.index') }}">
                            <i class="fas fa-building me-2"></i>Manage Offices
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="#" id="openFullscreenBtn">
                            <i class="fas fa-expand me-2"></i>Open QR Code in New Window
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="office_location_id" class="form-label">Select Office Location</label>
                        <select class="form-select" id="office_location_id" required>
                            <option value="">-- Select Office Location --</option>
                            @foreach($offices as $office)
                                <option value="{{ $office->id }}">{{ $office->name }} - {{ $office->address }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Choose the office location where the QR code will be displayed</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="duration" class="form-label">QR Code Duration (seconds)</label>
                        <input type="number" class="form-control" id="duration" value="30" min="10" max="3600">
                        <div class="form-text">QR code will expire after this duration and regenerate</div>
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
                <li>Select an office location from the dropdown</li>
                <li>Set the duration for how long the QR code should be valid</li>
                <li>Click "Generate QR Code" to create a new QR code</li>
                <li>Click "Open QR Code in New Window" from the menu to display it in a popup</li>
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
    let currentQRData = null;
    let currentOfficeName = '';
    let currentOfficeAddress = '';
    let currentDuration = 30;
    let qrPopupWindow = null;
    
    document.getElementById('generateBtn').addEventListener('click', generateQRCode);
    document.getElementById('openFullscreenBtn').addEventListener('click', openPopupWindow);
    
    function generateQRCode() {
        const officeLocationId = document.getElementById('office_location_id').value;
        const duration = document.getElementById('duration').value || 30;
        
        if (!officeLocationId) {
            alert('Please select an office location');
            return;
        }
        
        currentDuration = duration;
        
        // Get office name and address
        const selectedOffice = document.getElementById('office_location_id').selectedOptions[0].text;
        const officeParts = selectedOffice.split(' - ');
        currentOfficeName = officeParts[0] || 'Office';
        currentOfficeAddress = officeParts[1] || '';
        
        // Send request to generate QR code
        fetch('/admin/qrcode/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                office_location_id: officeLocationId,
                duration: duration
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentQRData = data.qr_code;
                
                // Clear previous QR code
                if (qrCodeInstance) {
                    qrCodeInstance.clear();
                    document.getElementById('qrCode').innerHTML = '';
                }
                
                // Create new QR code
                qrCodeInstance = new QRCode(document.getElementById('qrCode'), {
                    text: currentQRData,
                    width: 200,
                    height: 200,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H
                });
                
                // Update popup window if it's open
                if (qrPopupWindow && !qrPopupWindow.closed) {
                    updatePopupQRCode();
                }
                
                // Reset and start countdown
                clearInterval(countdownInterval);
                timeLeft = parseInt(duration);
                document.getElementById('timeLeft').textContent = timeLeft;
                
                countdownInterval = setInterval(() => {
                    timeLeft--;
                    document.getElementById('timeLeft').textContent = timeLeft;
                    
                    // Update popup countdown
                    if (qrPopupWindow && !qrPopupWindow.closed) {
                        try {
                            const countdownElement = qrPopupWindow.document.getElementById('timeLeft');
                            if (countdownElement) {
                                countdownElement.textContent = timeLeft;
                            }
                        } catch (e) {
                            console.log('Cannot update popup countdown');
                        }
                    }
                    
                    if (timeLeft <= 0) {
                        clearInterval(countdownInterval);
                        generateQRCode();
                    }
                }, 1000);
            } else {
                alert('Error generating QR code: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error generating QR code');
        });
    }
    
    function openPopupWindow() {
        if (!currentQRData) {
            alert('Please generate a QR code first');
            return;
        }
        
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
    }
    
    function createPopupContent() {
        const doc = qrPopupWindow.document;
        
        const htmlContent = '<!DOCTYPE html>' +
            '<html>' +
            '<head>' +
            '<meta charset="utf-8">' +
            '<meta name="viewport" content="width=device-width, initial-scale=1">' +
            '<title>QR Code Display - ' + currentOfficeName + '</title>' +
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
            '.qr-title { font-size: 3rem; font-weight: bold; margin-bottom: 1rem; }' +
            '.qr-subtitle { font-size: 1.5rem; margin-bottom: 0.5rem; }' +
            '.qr-address { font-size: 1.2rem; opacity: 0.9; margin-bottom: 3rem; }' +
            '.qr-box {' +
            '    background: white;' +
            '    padding: 40px;' +
            '    border-radius: 20px;' +
            '    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);' +
            '    display: inline-block;' +
            '    margin-bottom: 2rem;' +
            '}' +
            '.countdown-badge {' +
            '    background: white;' +
            '    color: #667eea;' +
            '    padding: 15px 40px;' +
            '    border-radius: 50px;' +
            '    font-size: 1.5rem;' +
            '    font-weight: bold;' +
            '    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);' +
            '}' +
            '.footer-text { margin-top: 2rem; font-size: 1.2rem; opacity: 0.9; }' +
            '</style>' +
            '</head>' +
            '<body>' +
            '<div class="qr-container">' +
            '<div class="qr-title"><i class="fas fa-qrcode me-3"></i>Scan to Check In</div>' +
            '<div class="qr-subtitle">' + currentOfficeName + '</div>' +
            '<div class="qr-address">' + currentOfficeAddress + '</div>' +
            '<div class="qr-box"><div id="qrCodeDisplay"></div></div>' +
            '<div class="countdown-badge">' +
            '<i class="fas fa-clock me-2"></i>' +
            'Expires in: <span id="timeLeft">' + timeLeft + '</span> seconds' +
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
            '            text: "' + currentQRData + '",' +
            '            width: 450,' +
            '            height: 450,' +
            '            colorDark: "#000000",' +
            '            colorLight: "#ffffff",' +
            '            correctLevel: QRCode.CorrectLevel.H' +
            '        });' +
            '    } catch(e) {' +
            '        console.error("QR Code Error:", e);' +
            '    }' +
            '};' +
            '<\/script>' +
            '</body>' +
            '</html>';
        
        doc.open();
        doc.write(htmlContent);
        doc.close();
    }
    
    function updatePopupQRCode() {
        if (qrPopupWindow && !qrPopupWindow.closed) {
            try {
                const qrContainer = qrPopupWindow.document.getElementById('qrCodeDisplay');
                if (qrContainer && qrPopupWindow.QRCode) {
                    qrContainer.innerHTML = '';
                    new qrPopupWindow.QRCode(qrContainer, {
                        text: currentQRData,
                        width: 450,
                        height: 450,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: qrPopupWindow.QRCode.CorrectLevel.H
                    });
                }
            } catch (e) {
                console.log('Cannot update popup QR code:', e);
            }
        }
    }
    
    // Generate initial QR code on load
    document.addEventListener('DOMContentLoaded', function() {
        const officeSelect = document.getElementById('office_location_id');
        officeSelect.addEventListener('change', function() {
            if (this.value) {
                generateQRCode();
            }
        });
    });
</script>
@endsection