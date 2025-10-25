<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'office_location_id', 'generated_at', 'expires_at', 'is_active'
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function officeLocation()
    {
        return $this->belongsTo(OfficeLocation::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function isValid()
    {
        return $this->is_active && $this->expires_at->isFuture();
    }
}
