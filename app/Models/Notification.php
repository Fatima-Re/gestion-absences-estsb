<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'data',
        'read_at',
        'related_model',
        'related_id',
        'action_url',
        'priority'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'data' => 'array',
        'priority' => 'integer'
    ];

    public const TYPE_INFO = 'info';
    public const TYPE_WARNING = 'warning';
    public const TYPE_SUCCESS = 'success';
    public const TYPE_ERROR = 'error';
    public const TYPE_ALERT = 'alert';

    public const PRIORITY_LOW = 1;
    public const PRIORITY_NORMAL = 2;
    public const PRIORITY_HIGH = 3;
    public const PRIORITY_URGENT = 4;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function markAsRead()
    {
        $this->read_at = now();
        $this->save();
    }

    public function markAsUnread()
    {
        $this->read_at = null;
        $this->save();
    }

    public function isRead()
    {
        return !is_null($this->read_at);
    }

    public function isUnread()
    {
        return is_null($this->read_at);
    }
}