<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LateReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;
    protected $superiorId;

    public function __construct($superiorId, $filters = [])
    {
        $this->superiorId = $superiorId;
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $teamMembers = \App\Models\User::where('superior_id', $this->superiorId)->get();
        $query = Attendance::where('status', 'late')
            ->whereIn('user_id', $teamMembers->pluck('id'));

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('check_in_time', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('check_in_time', '<=', $this->filters['end_date']);
        }

        if (!empty($this->filters['employee_id'])) {
            $query->where('user_id', $this->filters['employee_id']);
        }

        return $query->with('user')->orderBy('check_in_time', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Employee Name',
            'Employee Email',
            'Check In Time',
            'Scheduled Time',
            'Late Duration (min)',
            'Status',
            'Created At',
        ];
    }

    public function map($attendance): array
    {
        // Calculate late duration based on scheduled time
        $scheduledTime = \Carbon\Carbon::parse(config('app.check_in_start_time', '08:00'));
        $checkInTime = \Carbon\Carbon::parse($attendance->check_in_time);
        
        $lateDuration = 0;
        if ($checkInTime->gt($scheduledTime)) {
            $lateDuration = $scheduledTime->diffInMinutes($checkInTime);
        }

        return [
            $attendance->id,
            $attendance->user ? $attendance->user->name : 'N/A',
            $attendance->user ? $attendance->user->email : 'N/A',
            $attendance->check_in_time ? $attendance->check_in_time->timezone('Asia/Jakarta')->format('d M Y H:i:s') : 'N/A',
            $scheduledTime->format('H:i'),
            $lateDuration,
            $this->formatStatus($attendance->status),
            $attendance->created_at->timezone('Asia/Jakarta')->format('d M Y H:i:s'),
        ];
    }

    protected function formatStatus($status)
    {
        switch ($status) {
            case 'on_time':
                return 'On Time';
            case 'late':
                return 'Late';
            case 'absent':
                return 'Absent';
            default:
                return ucfirst($status);
        }
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }
}