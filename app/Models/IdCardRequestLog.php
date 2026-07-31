<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdCardRequestLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id_card_request_id',
        'user_id',
        'user_name',
        'from_status',
        'to_status',
        'action_description',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(IdCardRequest::class, 'id_card_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
