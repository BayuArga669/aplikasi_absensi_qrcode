<?php
// app/Http/Controllers/Admin/QrCodeController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\QrCodeService;
use App\Models\OfficeLocation;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;

class QrCodeController extends Controller
{
    protected $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    public function index()
    {
        $officeLocations = OfficeLocation::where('is_active', true)->get();
        return view('admin.qrcode.index', compact('officeLocations'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'office_location_id' => 'required|exists:office_locations,id',
        ]);

        $qrCode = $this->qrCodeService->generateQrCode(
            $request->office_location_id,
            30 // 30 seconds expiry
        );

        $qrCodeImage = QrCodeGenerator::size(300)->generate($qrCode->code);

        return response()->json([
            'success' => true,
            'data' => [
                'code' => $qrCode->code,
                'expires_at' => $qrCode->expires_at->toIso8601String(),
                'qr_image' => $qrCodeImage,
            ],
        ]);
    }

    public function display($officeLocationId)
    {
        $officeLocation = OfficeLocation::findOrFail($officeLocationId);
        return view('admin.qrcode.display', compact('officeLocation'));
    }
}