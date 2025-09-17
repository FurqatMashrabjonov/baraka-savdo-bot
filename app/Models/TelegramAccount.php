<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TelegramAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'chat_id',
        'username',
        'first_name',
        'last_name',
        'lang',
        'channel_joined_at',
    ];

    public function client(): BelongsTo
    {
        return $this->hasOne(Client::class, 'chat_id', 'chat_id');
    }

    public function scopeChatId($chatId)
    {
        return $this->where('chat_id', $chatId);
    }
}
