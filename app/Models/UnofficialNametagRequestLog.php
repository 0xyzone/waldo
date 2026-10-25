<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnofficialNametagRequestLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'unofficial_nametag_request_id',
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
            'user_id' => 'integer',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(UnofficialNametagRequest::class, 'unofficial_nametag_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
