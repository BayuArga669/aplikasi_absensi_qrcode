<?php
// app/Models/WorkSchedule.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'check_in_time', 'check_out_time', 'late_tolerance', 'is_default'
    ];

    protected $casts = [
        'late_tolerance' => 'integer',
        'is_default' => 'boolean',
    ];

    public function userSchedules()
    {
        return $this->hasMany(UserSchedule::class);
    }
}
