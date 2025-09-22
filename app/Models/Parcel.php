<?php

namespace App\Models;

use App\Enums\ParcelStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Parcel extends Model
{
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

    public static function findOrCreateByTrackNumber(string $trackNumber, ?int $clientId = null): self
    {
        $attributes = ['track_number' => $trackNumber];

        if ($clientId) {
            $attributes['client_id'] = $clientId;
        }

        return self::firstOrCreate(['track_number' => $trackNumber], $attributes);
    }

    public function getStatusLabel(): string
    {
        return $this->status->getLabel();
    }

    public function getProgressSteps(): array
    {
        return [
            [
                'name' => 'Yaratildi',
                'status' => ParcelStatus::CREATED,
                'completed' => $this->status->value >= ParcelStatus::CREATED->value,
                'label' => 'Yaratildi',
                'date' => $this->created_at,
            ],
            [
                'name' => 'Xitoyga yetib keldi',
                'status' => ParcelStatus::ARRIVED_CHINA,
                'completed' => $this->status->value >= ParcelStatus::ARRIVED_CHINA->value,
                'label' => 'Xitoyga yetib keldi',
                'date' => $this->china_uploaded_at,
            ],
            [
                'name' => 'O\'zbekistonga yetib keldi',
                'status' => ParcelStatus::ARRIVED_UZB,
                'completed' => $this->status->value >= ParcelStatus::ARRIVED_UZB->value,
                'label' => 'O\'zbekistonga yetib keldi',
                'date' => $this->uzb_uploaded_at,
            ],
            [
                'name' => 'Yetkazib berildi',
                'status' => ParcelStatus::DELIVERED,
                'completed' => $this->status->value >= ParcelStatus::DELIVERED->value,
                'label' => 'Yetkazib berildi',
                'date' => $this->status === ParcelStatus::DELIVERED ? $this->updated_at : null,
            ],
        ];
    }

}
