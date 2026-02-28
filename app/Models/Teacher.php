<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'teacher_code',
        'cin',
        'departement',
        'grade',
        'phone',
        'office',
        'hire_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'module_teacher')
                    ->withPivot('role', 'assigned_hours', 'is_active')
                    ->withTimestamps();
    }

    public function groups()
    {
        return $this->hasManyThrough(
            Group::class,
            CourseSession::class,
            'teacher_id',
            'id',
            'id',
            'group_id'
        )->distinct();
    }

    public function courseSessions()
    {
        return $this->hasMany(CourseSession::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class, 'recorded_by');
    }

    public function getFullNameAttribute()
    {
        return $this->user->name;
    }

    public function getEmailAttribute()
    {
        return $this->user->email;
    }

    public function getContactPhoneAttribute()
    {
        return $this->user->phone;
    }
}