<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'client_id',
    ];

    protected function casts(): array
    {
        return [
            'channel_joined_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function scopeChatId($query, string $chatId)
    {
        return $query->where('chat_id', $chatId);
    }

    public static function findByChatId(string $chatId): ?self
    {
        return static::where('chat_id', $chatId)->first();
    }

    public function hasClient(): bool
    {
        return ! is_null($this->client_id) && $this->client()->exists();
    }
}
