<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'full_name',
        'phone',
        'telegram_id',
        'telegram_username',
    ];

    public function parcels(): HasMany
    {
        return $this->hasMany(Parcel::class);
    }
}
