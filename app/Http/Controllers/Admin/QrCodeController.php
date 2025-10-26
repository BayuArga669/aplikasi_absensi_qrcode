<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QrCode as QrCodeModel;
use App\Models\OfficeLocation;
use Carbon\Carbon;

class QrCodeController extends Controller
{
    public function index()
    {
        $offices = OfficeLocation::all();
        return view('admin.qr_generator', compact('offices'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'office_location_id' => 'required|exists:office_locations,id',
            'duration' => 'required|integer|min:10|max:3600' // Minimum 10 seconds, maximum 1 hour
        ]);

        $officeLocation = OfficeLocation::findOrFail($request->office_location_id);

        // Deactivate old QR codes for this location
        QrCodeModel::where('office_location_id', $officeLocation->id)
            ->update(['is_active' => false]);

        // Generate a new QR code
        $qrCode = QrCodeModel::create([
            'code' => uniqid('qr_', true),
            'office_location_id' => $officeLocation->id,
            'generated_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addSeconds($request->duration),
            'is_active' => true
        ]);
        
        return response()->json([
            'success' => true,
            'qr_code' => $qrCode->code,
            'expires_at' => $qrCode->expires_at
        ]);
    }

    public function display($officeLocationId)
    {
        $officeLocation = OfficeLocation::findOrFail($officeLocationId);

        // Display the QR code for the specified office location
        $currentQrCode = QrCodeModel::where('office_location_id', $officeLocation->id)
            ->where('expires_at', '>', Carbon::now())
            ->where('is_active', true)
            ->first();
            
        if (!$currentQrCode) {
            // Generate a new QR code if none is valid
            $currentQrCode = QrCodeModel::create([
                'code' => uniqid('qr_', true),
                'office_location_id' => $officeLocation->id,
                'generated_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addSeconds(30),
                'is_active' => true
            ]);
        }
        
        return view('admin.qr_display', compact('currentQrCode', 'officeLocation'));
    }

    public function showLargeQr($officeLocationId)
    {
        $officeLocation = OfficeLocation::findOrFail($officeLocationId);

        // Display the QR code for the specified office location
        $currentQrCode = QrCodeModel::where('office_location_id', $officeLocation->id)
            ->where('expires_at', '>', Carbon::now())
            ->where('is_active', true)
            ->first();
            
        if (!$currentQrCode) {
            // Generate a new QR code if none is valid
            $currentQrCode = QrCodeModel::create([
                'code' => uniqid('qr_', true),
                'office_location_id' => $officeLocation->id,
                'generated_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addSeconds(30),
                'is_active' => true
            ]);
        }
        
        return view('admin.qr_large_display', compact('currentQrCode', 'officeLocation'));
    }
}