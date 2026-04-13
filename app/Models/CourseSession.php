<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class CourseSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'group_id',
        'teacher_id',
        'date',
        'start_time',
        'end_time',
        'room',
        'topic',
        'description',
        'status',
        'is_cancelled',
        'cancellation_reason'
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_cancelled' => 'boolean'
    ];

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class, 'session_id');
    }

    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED)
                     ->orWhere('is_cancelled', true);
    }

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeForGroup($query, $groupId)
    {
        return $query->where('group_id', $groupId);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Attendance can be modified only within the configured window after session date.
     */
    public function canBeModified(): bool
    {
        if ($this->status === self::STATUS_CANCELLED || $this->is_cancelled) {
            return false;
        }

        $hours = Setting::getTeacherModificationPeriod();
        $sessionDate = $this->date instanceof Carbon
            ? $this->date->copy()
            : Carbon::parse((string) $this->date);

        return $sessionDate->diffInHours(now()) <= $hours;
    }
}