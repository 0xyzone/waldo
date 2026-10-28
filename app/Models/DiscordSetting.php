<?php

namespace App\Models;

use App\Traits\HasActionLogs;
use Illuminate\Database\Eloquent\Model;

class DiscordSetting extends Model
{
    use HasActionLogs;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'bot_token',
        'guild_id',
        'target_channel_id',
    ];
}
