<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'telegram_account_id',
        'message',
        'admin_reply',
        'replied_at',
        'replied_by',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'replied_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function telegramAccount(): BelongsTo
    {
        return $this->belongsTo(TelegramAccount::class);
    }

    public function repliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isReplied(): bool
    {
        return $this->status === 'replied';
    }

    public function markAsReplied(int $adminId, string $reply): void
    {
        $this->update([
            'admin_reply' => $reply,
            'replied_at' => now(),
            'replied_by' => $adminId,
            'status' => 'replied',
        ]);
    }
}
