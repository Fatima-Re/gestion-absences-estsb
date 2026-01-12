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
        'is_active'
    ];

    protected $casts = [
        'max_students' => 'integer',
        'is_active' => 'boolean'
    ];

    public function students()
    {
        return $this->hasMany(Student::class);
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