<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'chat_id',
        'phone',
        'full_name',
        'address',
    ];

    public function telegramAccount()
    {
        return $this->hasOne(TelegramAccount::class, 'chat_id', 'chat_id');
    }

    public function telegram()
    {
        return $this->hasOne(TelegramAccount::class, 'chat_id', 'chat_id');
    }
}
