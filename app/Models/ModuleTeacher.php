<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ModuleTeacher extends Pivot
{
    protected $table = 'module_teacher';

    public $incrementing = true;

    protected $fillable = [
        'module_id',
        'teacher_id',
        'role',
        'academic_year',
        'semester',
        'assigned_hours',
        'is_active'
    ];

  
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}