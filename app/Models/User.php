<?php

// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'employee_id', 
        'position', 'department', 'superior_id', 'phone', 'is_active'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function superior()
    {
        return $this->belongsTo(User::class, 'superior_id');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'superior_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function userSchedules()
    {
        return $this->hasMany(UserSchedule::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isSuperior()
    {
        return $this->role === 'superior';
    }

    public function isEmployee()
    {
        return $this->role === 'employee';
    }
}
