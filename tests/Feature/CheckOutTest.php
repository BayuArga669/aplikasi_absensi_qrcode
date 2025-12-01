<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\OfficeLocation;
use App\Models\QrCode;
use Carbon\Carbon;

class CheckOutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_prevents_early_check_out_before_deadline()
    {
        // Set up office location with check-out deadline at 5 PM
        $officeLocation = OfficeLocation::factory()->create([
            'check_out_deadline' => '17:00:00' // 5 PM
        ]);

        $qrCode = QrCode::factory()->create([
            'office_location_id' => $officeLocation->id
        ]);

        $user = User::factory()->create();
        
        // Create an attendance record with check-in time
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'qr_code_id' => $qrCode->id,
            'check_in_time' => Carbon::today()->setTime(9, 0, 0),
            'check_in_latitude' => -6.200000,
            'check_in_longitude' => 106.816666,
            'status' => 'on_time',
            'is_late' => false,
        ]);

        // Mock a time before the check-out deadline (e.g., 3 PM)
        Carbon::setTestNow(Carbon::today()->setTime(15, 0, 0)); // 3 PM

        // Initialize service with mocked dependencies
        $geolocationService = $this->createMock(\App\Services\GeolocationService::class);
        $notificationService = $this->createMock(\App\Services\NotificationService::class);

        $service = new \App\Services\AttendanceService($geolocationService, $notificationService);

        // Mock geolocation validation to pass
        $geolocationService->expects($this->once())
            ->method('isWithinRadius')
            ->willReturn(true);

        // Attempt to check out before the deadline
        $result = $service->checkOut($user, $qrCode, -6.200000, 106.816666);

        // Assert that check-out is prevented
        $this->assertFalse($result['success']);
        $this->assertEquals('Check-out is not allowed before 17:00', $result['message']);

        Carbon::setTestNow(); // Reset time
    }

    /** @test */
    public function it_allows_check_out_after_deadline()
    {
        // Set up office location with check-out deadline at 5 PM
        $officeLocation = OfficeLocation::factory()->create([
            'check_out_deadline' => '17:00:00' // 5 PM
        ]);

        $qrCode = QrCode::factory()->create([
            'office_location_id' => $officeLocation->id
        ]);

        $user = User::factory()->create();
        
        // Create an attendance record with check-in time
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'qr_code_id' => $qrCode->id,
            'check_in_time' => Carbon::today()->setTime(9, 0, 0),
            'check_in_latitude' => -6.200000,
            'check_in_longitude' => 106.816666,
            'status' => 'on_time',
            'is_late' => false,
        ]);

        // Mock a time after the check-out deadline (e.g., 6 PM)
        Carbon::setTestNow(Carbon::today()->setTime(18, 0, 0)); // 6 PM

        // Initialize service with mocked dependencies
        $geolocationService = $this->createMock(\App\Services\GeolocationService::class);
        $notificationService = $this->createMock(\App\Services\NotificationService::class);

        $service = new \App\Services\AttendanceService($geolocationService, $notificationService);

        // Mock geolocation validation to pass
        $geolocationService->expects($this->once())
            ->method('isWithinRadius')
            ->willReturn(true);

        // Attempt to check out after the deadline
        $result = $service->checkOut($user, $qrCode, -6.200000, 106.816666);

        // Assert that check-out is allowed
        $this->assertTrue($result['success']);
        $this->assertEquals('Check-out successful', $result['message']);

        Carbon::setTestNow(); // Reset time
    }
}