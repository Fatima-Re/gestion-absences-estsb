<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'level',
        'specialty',
        'max_students',
        'academic_year',
        'semester',
        'is_active',
        'description',
        'teacher_id',
    ];

    protected $casts = [
        'max_students' => 'integer',
        'is_active' => 'boolean',
        'semester' => 'integer',
    ];

    public function students()
    {
        return $this->belongsToMany(Student::class, 'group_student')
                    ->withPivot('joined_at', 'left_at', 'is_active')
                    ->withTimestamps();
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'group_module')
                    ->withTimestamps();
    }

    public function courseSessions()
    {
        return $this->hasMany(CourseSession::class);
    }

    public function activeStudents()
    {
        return $this->students()->whereHas('user', function($query) {
            $query->where('is_active', true);
        });
    }

    public function studentsCount()
    {
        return $this->activeStudents()->count();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByAcademicYear($query, $year)
    {
        return $query->where('academic_year', $year);
    }
}