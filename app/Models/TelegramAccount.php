<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TelegramAccount extends Model
{
    use HasFactory, SoftDeletes;

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
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Find telegram account by chat ID
     */
    public static function findByChatId(string $chatId): ?self
    {
        return static::where('chat_id', $chatId)->first();
    }

    /**
     * Create or update telegram account by chat ID
     */
    public static function updateOrCreateByChatId(string $chatId, array $data): self
    {
        return static::updateOrCreate(
            ['chat_id' => $chatId],
            $data
        );
    }
}
