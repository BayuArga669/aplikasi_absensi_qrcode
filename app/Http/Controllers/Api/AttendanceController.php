<?php

// app/Http/Controllers/Api/AttendanceController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use App\Services\QrCodeService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected $attendanceService;
    protected $qrCodeService;

    public function __construct(
        AttendanceService $attendanceService,
        QrCodeService $qrCodeService
    ) {
        $this->attendanceService = $attendanceService;
        $this->qrCodeService = $qrCodeService;
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Validate QR code
        $qrCode = $this->qrCodeService->validateQrCode($request->qr_code);

        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired QR code',
            ], 400);
        }

        // Process check-in
        $result = $this->attendanceService->checkIn(
            $request->user(),
            $qrCode,
            $request->latitude,
            $request->longitude
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Validate QR code
        $qrCode = $this->qrCodeService->validateQrCode($request->qr_code);

        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired QR code',
            ], 400);
        }

        $result = $this->attendanceService->checkOut(
            $request->user(),
            $qrCode,
            $request->latitude,
            $request->longitude
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function history(Request $request)
    {
        $attendances = $request->user()
            ->attendances()
            ->orderBy('check_in_time', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $attendances,
        ]);
    }

    public function today(Request $request)
    {
        $attendance = $request->user()
            ->attendances()
            ->whereDate('check_in_time', today())
            ->first();

        return response()->json([
            'success' => true,
            'data' => $attendance,
        ]);
    }
}