<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class GroupModule extends Pivot
{
    protected $table = 'group_module';

    public $incrementing = true;

    protected $fillable = [
        'group_id',
        'module_id',
        'academic_year',
        'semester'
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}