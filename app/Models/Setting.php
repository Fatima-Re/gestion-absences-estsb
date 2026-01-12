<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'group',
        'is_editable',
        'options'
    ];

    protected $casts = [
        'is_editable' => 'boolean',
        'options' => 'array'
    ];

    public const TYPE_STRING = 'string';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_ARRAY = 'array';
    public const TYPE_JSON = 'json';

    public const GROUP_GENERAL = 'general';
    public const GROUP_ATTENDANCE = 'attendance';
    public const GROUP_NOTIFICATIONS = 'notifications';
    public const GROUP_SYSTEM = 'system';

    public function scopeByGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    public function scopeEditable($query)
    {
        return $query->where('is_editable', true);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_editable', false);
    }

    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function setValue($key, $value)
    {
        $setting = self::where('key', $key)->first();
        
        if ($setting) {
            $setting->value = $value;
            $setting->save();
        } else {
            self::create([
                'key' => $key,
                'value' => $value,
                'type' => self::TYPE_STRING,
                'group' => self::GROUP_GENERAL,
                'is_editable' => true
            ]);
        }
    }

    // Helper methods for common settings
    public static function getAbsenceWarningThreshold()
    {
        return (int) self::getValue('absence_warning_threshold', 85);
    }

    public static function getAbsenceAlertThreshold()
    {
        return (int) self::getValue('absence_alert_threshold', 75);
    }

    public static function getAbsenceCriticalThreshold()
    {
        return (int) self::getValue('absence_critical_threshold', 60);
    }

    public static function getTeacherModificationPeriod()
    {
        return (int) self::getValue('teacher_modification_period', 48); // hours
    }

    public static function getStudentJustificationPeriod()
    {
        return (int) self::getValue('student_justification_period', 7); // days
    }

    public static function getMedicalCertificateValidity()
    {
        return (int) self::getValue('medical_certificate_validity', 3); // days
    }
}