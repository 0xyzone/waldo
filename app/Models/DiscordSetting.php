<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscordSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'bot_token',
        'guild_id',
        'target_channel_id',
    ];
}
