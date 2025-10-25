<?php

// app/Services/QrCodeService.php
namespace App\Services;

use App\Models\QrCode;
use App\Models\OfficeLocation;
use Illuminate\Support\Str;
use Carbon\Carbon;

class QrCodeService
{
    public function generateQrCode(int $officeLocationId, int $expirySeconds = 30): QrCode
    {
        // Deactivate old QR codes for this location
        QrCode::where('office_location_id', $officeLocationId)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Generate new QR code
        $code = $this->generateUniqueCode();
        
        return QrCode::create([
            'code' => $code,
            'office_location_id' => $officeLocationId,
            'generated_at' => now(),
            'expires_at' => now()->addSeconds($expirySeconds),
            'is_active' => true,
        ]);
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = Str::random(32) . '-' . time();
        } while (QrCode::where('code', $code)->exists());

        return $code;
    }

    public function validateQrCode(string $code): ?QrCode
    {
        $qrCode = QrCode::where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$qrCode || !$qrCode->isValid()) {
            return null;
        }

        return $qrCode;
    }

    public function getActiveQrCode(int $officeLocationId): ?QrCode
    {
        return QrCode::where('office_location_id', $officeLocationId)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();
    }
}