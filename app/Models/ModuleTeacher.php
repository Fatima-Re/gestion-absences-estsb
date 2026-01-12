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
        'is_responsible',
        'academic_year',
        'semester'
    ];

    protected $casts = [
        'is_responsible' => 'boolean'
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