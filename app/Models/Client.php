<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'full_name',
        'phone',
        'address',
    ];

    public function parcels(): HasMany
    {
        return $this->hasMany(Parcel::class);
    }

    public function telegramAccount(): HasOne
    {
        return $this->hasOne(TelegramAccount::class);
    }

    public static function findByChatId(string $chatId): ?self
    {
        $telegramAccount = TelegramAccount::findByChatId($chatId);

        return $telegramAccount?->client;
    }

    public function isComplete(): bool
    {
        return !empty($this->full_name) &&
               !empty($this->phone) &&
               !empty($this->address);
    }
}
