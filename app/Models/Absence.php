<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'session_id',
        'attendance_record_id',
        'status',
        'justification_id',
        'absence_type',
        'notes'
    ];

    public const STATUS_UNJUSTIFIED = 'unjustified';
    public const STATUS_JUSTIFIED = 'justified';
    public const STATUS_PENDING = 'pending';

    public const TYPE_ABSENCE = 'absence';
    public const TYPE_LATE = 'late';

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function session()
    {
        return $this->belongsTo(CourseSession::class);
    }

    public function attendanceRecord()
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    public function justification()
    {
        return $this->belongsTo(Justification::class);
    }

    public function scopeUnjustified($query)
    {
        return $query->where('status', self::STATUS_UNJUSTIFIED);
    }

    public function scopeJustified($query)
    {
        return $query->where('status', self::STATUS_JUSTIFIED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForModule($query, $moduleId)
    {
        return $query->whereHas('session', function($q) use ($moduleId) {
            $q->where('module_id', $moduleId);
        });
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereHas('session', function($q) use ($startDate, $endDate) {
            $q->whereBetween('date', [$startDate, $endDate]);
        });
    }

    public function isJustifiable()
    {
        // Check if within 7 days for students
        $createdAt = $this->created_at ?: now();
        $daysSince = $createdAt->diffInDays(now());
        
        return $daysSince <= 7 && $this->status === self::STATUS_UNJUSTIFIED;
    }
}
