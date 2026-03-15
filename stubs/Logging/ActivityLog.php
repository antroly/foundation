<?php

declare(strict_types=1);

namespace App\Logging;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'logs';

    protected $fillable = [
        'level',
        'message',
        'context',
        'url',
        'ip',
        'user_id',
    ];

    protected $casts = [
        'context'    => 'array',
        'created_at' => 'datetime',
    ];
}
