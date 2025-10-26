<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QrCode as QrCodeModel;
use Carbon\Carbon;

class QrCodeController extends Controller
{
    public function index()
    {
        return view('admin.qr_generator');
    }

    public function generate(Request $request)
    {
        // Generate a new QR code
        $qrCode = QrCodeModel::create([
            'code' => uniqid('qr_', true),
            'valid_from' => Carbon::now(),
            'valid_until' => Carbon::now()->addSeconds($request->duration ?? 30),
            'office_location' => $request->office_location ?? 'Main Office',
            'created_by' => auth()->id()
        ]);
        
        return response()->json([
            'success' => true,
            'qr_code' => $qrCode->code,
            'expires_at' => $qrCode->valid_until
        ]);
    }

    public function display($officeLocation)
    {
        // Display the QR code for the specified office location
        $currentQrCode = QrCodeModel::where('office_location', $officeLocation)
            ->where('valid_until', '>', Carbon::now())
            ->first();
            
        if (!$currentQrCode) {
            // Generate a new QR code if none is valid
            $currentQrCode = QrCodeModel::create([
                'code' => uniqid('qr_', true),
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addSeconds(30),
                'office_location' => $officeLocation,
                'created_by' => auth()->id()
            ]);
        }
        
        return view('admin.qr_display', compact('currentQrCode'));
    }
}