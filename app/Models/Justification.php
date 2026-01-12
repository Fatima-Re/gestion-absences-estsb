<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Justification extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'absence_id',
        'attendance_record_id',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'file_mime',
        'description',
        'start_date',
        'end_date',
        'submitted_at',
        'validated_by',
        'status',
        'validation_date',
        'rejection_reason',
        'comments'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'validation_date' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
        'file_size' => 'integer'
    ];

    public const TYPE_MEDICAL = 'medical';
    public const TYPE_OFFICIAL = 'official';
    public const TYPE_PERSONAL = 'personal';
    public const TYPE_TRANSPORT = 'transport';
    public const TYPE_OTHER = 'other';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_NEEDS_INFO = 'needs_info';

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function absence()
    {
        return $this->belongsTo(Absence::class);
    }

    public function attendanceRecord()
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeNeedsInfo($query)
    {
        return $query->where('status', self::STATUS_NEEDS_INFO);
    }

    public function scopeMedical($query)
    {
        return $query->where('type', self::TYPE_MEDICAL);
    }

    public function scopeSubmittedAfter($query, $date)
    {
        return $query->where('submitted_at', '>', $date);
    }

    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    public function isValidMedicalCertificate()
    {
        if ($this->type !== self::TYPE_MEDICAL) {
            return true;
        }

        // Check if submitted within 3 days after return
        $submittedAt = $this->submitted_at ?: now();
        $endDate = $this->end_date;
        
        if (!$endDate) {
            return false;
        }

        $daysBetween = $endDate->diffInDays($submittedAt);
        return $daysBetween <= 3;
    }
}