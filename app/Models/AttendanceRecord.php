<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'student_id',
        'status',
        'comments',
        'recorded_by',
        'recorded_at',
        'is_late',
        'late_minutes'
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'is_late' => 'boolean',
        'late_minutes' => 'integer'
    ];

    public const STATUS_PRESENT = 'present';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_EXCUSED = 'excused';
    public const STATUS_LATE = 'late';

    public function session()
    {
        return $this->belongsTo(CourseSession::class, 'session_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'recorded_by');
    }

    public function justifications()
    {
        return $this->hasMany(Justification::class);
    }

    public function absence()
    {
        return $this->hasOne(Absence::class);
    }

    public function isAbsent()
    {
        return $this->status === self::STATUS_ABSENT;
    }

    public function isPresent()
    {
        return $this->status === self::STATUS_PRESENT;
    }

    public function isExcused()
    {
        return $this->status === self::STATUS_EXCUSED;
    }

    public function isLate()
    {
        return $this->is_late || $this->status === self::STATUS_LATE;
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', self::STATUS_ABSENT);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }
}