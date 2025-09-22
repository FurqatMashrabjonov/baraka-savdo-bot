<?php

namespace App\Models;

use App\Enums\ParcelStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Parcel extends Model
{
    use HasFactory;

    protected $fillable = [
        'track_number',
        'client_id',
        'weight',
        'status',
        'is_banned',
        'china_uploaded_at',
        'uzb_uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ParcelStatus::class,
            'is_banned' => 'boolean',
            'weight' => 'decimal:3',
            'china_uploaded_at' => 'datetime',
            'uzb_uploaded_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get weight with KG suffix and ceil to .5 precision
     */
    public function getFormattedWeightAttribute(): string
    {
        if (!$this->weight) {
            return '—';
        }

        // Ceil to nearest 0.5
        $ceiledWeight = ceil($this->weight * 2) / 2;

        return number_format($ceiledWeight, 1) . ' KG';
    }
}
