<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'credits',
        'hours',
        'semester',
        'academic_year',
        'is_active'
    ];

    protected $casts = [
        'credits' => 'integer',
        'hours' => 'integer',
        'is_active' => 'boolean'
    ];

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'module_teacher')
                    ->withPivot('is_responsible')
                    ->withTimestamps();
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_module')
                    ->withTimestamps();
    }

    public function courseSessions()
    {
        return $this->hasMany(CourseSession::class);
    }

    public function absences()
    {
        return $this->hasManyThrough(Absence::class, CourseSession::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    public function scopeByAcademicYear($query, $year)
    {
        return $query->where('academic_year', $year);
    }
}
