<?php

// app/Services/ReportService.php
namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    public function getDailyReport(Carbon $date, ?int $userId = null): Collection
    {
        $query = Attendance::with('user')
            ->whereDate('check_in_time', $date);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->orderBy('check_in_time')->get();
    }

    public function getWeeklyReport(Carbon $startDate, ?int $userId = null): Collection
    {
        $endDate = $startDate->copy()->endOfWeek();

        $query = Attendance::with('user')
            ->whereBetween('check_in_time', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->orderBy('check_in_time')->get();
    }

    public function getMonthlyReport(int $year, int $month, ?int $userId = null): Collection
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = Attendance::with('user')
            ->whereBetween('check_in_time', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->orderBy('check_in_time')->get();
    }

    public function getLateArrivalsReport(Carbon $startDate, Carbon $endDate, ?int $superiorId = null): Collection
    {
        $query = Attendance::with('user')
            ->where('is_late', true)
            ->whereBetween('check_in_time', [$startDate, $endDate]);

        if ($superiorId) {
            $query->whereHas('user', function ($q) use ($superiorId) {
                $q->where('superior_id', $superiorId);
            });
        }

        return $query->orderBy('check_in_time', 'desc')->get();
    }
}