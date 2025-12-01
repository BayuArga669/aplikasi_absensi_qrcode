<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\Auth;

class AttendanceReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Attendance::with('user');

        // Apply filters
        if (!empty($this->filters['start_date'])) {
            $query->whereDate('check_in_time', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('check_in_time', '<=', $this->filters['end_date']);
        }

        if (!empty($this->filters['employee_id'])) {
            // Check if it's a single employee ID (for employee view) or multiple (for admin view)
            if (is_array($this->filters['employee_id'])) {
                $query->whereIn('user_id', $this->filters['employee_id']);
            } else {
                $query->where('user_id', $this->filters['employee_id']);
            }
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['department'])) {
            $query->whereHas('user', function ($q) {
                $q->where('department', 'like', '%' . $this->filters['department'] . '%');
            });
        }

        return $query->orderBy('check_in_time', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Employee Name',
            'Employee Email',
            'Check In Time',
            'Check Out Time',
            'Check In Location',
            'Check Out Location',
            'Status',
            'Is Late',
            'Late Duration (min)',
            'Notes',
            'Created At',
        ];
    }

    public function map($attendance): array
    {
        return [
            $attendance->id,
            $attendance->user ? $attendance->user->name : 'N/A',
            $attendance->user ? $attendance->user->email : 'N/A',
            $attendance->check_in_time ? $attendance->check_in_time->timezone('Asia/Jakarta')->format('d M Y H:i:s') : 'N/A',
            $attendance->check_out_time ? $attendance->check_out_time->timezone('Asia/Jakarta')->format('d M Y H:i:s') : 'N/A',
            $attendance->check_in_latitude . ', ' . $attendance->check_in_longitude,
            $attendance->check_out_latitude . ', ' . $attendance->check_out_longitude,
            $this->formatStatus($attendance->status),
            $attendance->is_late ? 'Yes' : 'No',
            $attendance->late_duration ?? 0,
            $attendance->notes ?? 'N/A',
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