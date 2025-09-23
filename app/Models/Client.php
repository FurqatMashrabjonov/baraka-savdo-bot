<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uid',
        'full_name',
        'phone',
        'address',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($client) {
            if (empty($client->uid)) {
                $client->uid = Str::uuid()->toString();
            }
        });
    }

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

    public static function findByUid(string $uid): ?self
    {
        return static::where('uid', $uid)->first();
    }

    public function isComplete(): bool
    {
        return !empty($this->full_name) &&
               !empty($this->phone) &&
               !empty($this->address);
    }
}
