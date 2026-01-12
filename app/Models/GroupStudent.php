<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class GroupStudent extends Pivot
{
    protected $table = 'group_student';

    public $incrementing = true;

    protected $fillable = [
        'group_id',
        'student_id',
        'academic_year',
        'semester',
        'joined_at',
        'left_at'
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime'
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}