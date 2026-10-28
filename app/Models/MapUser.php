<?php

namespace App\Models;

use App\Traits\HasActionLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapUser extends Model
{
    use HasActionLogs;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
