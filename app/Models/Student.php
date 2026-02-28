<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_number',
        // kept for backward compatibility, but the database column is now student_number
        //'student_code',
        'cin',
        'cne',
        'date_of_birth',
        'phone',
        'address',
        'filiere',
        'niveau',
        'academic_year',
        'photo',
        'group_id',
        'status'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // current group relationship (nullable)
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    // many-to-many relation through pivot; historical records
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_student')
                    ->withPivot('joined_at', 'left_at', 'is_active')
                    ->withTimestamps();
    }

    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function justifications()
    {
        return $this->hasMany(Justification::class);
    }

    public function getFullNameAttribute()
    {
        return $this->user->name;
    }

    public function getEmailAttribute()
    {
        return $this->user->email;
    }
}