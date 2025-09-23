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

    /**
     * Find or create parcel by track number
     */
    public static function findOrCreateByTrackNumber(string $trackNumber, ?int $clientId = null): self
    {
        $parcel = static::where('track_number', $trackNumber)->first();

        if (!$parcel) {
            $parcel = static::create([
                'track_number' => $trackNumber,
                'client_id' => $clientId,
                'status' => ParcelStatus::CREATED->value,
            ]);
        } elseif ($clientId && !$parcel->client_id) {
            $parcel->update(['client_id' => $clientId]);
        }

        return $parcel;
    }

    public function getStatusLabel(): string
    {
        $label = match ($this->status->value) {
            ParcelStatus::CREATED => __('dashboard.status.created'),
            ParcelStatus::ARRIVED_CHINA => __('dashboard.status.in_transit'),
            ParcelStatus::ARRIVED_UZB => __('dashboard.status.in_tashkent'),
            ParcelStatus::DELIVERED => __('dashboard.status.delivered'),
            default => __('dashboard.status.unknown'),
        };

        // Ensure we always return a string, even if translation fails
        return is_string($label) ? $label : 'Unknown Status';
    }

    /**
     * Get progress steps for tracking
     */
    public function getProgressSteps(): array
    {
        return [
            ParcelStatus::CREATED->value => [
                'label' => __('dashboard.status.created'),
                'completed' => true,
                'current' => $this->status === ParcelStatus::CREATED,
            ],
            ParcelStatus::ARRIVED_CHINA->value => [
                'label' => __('dashboard.status.arrived_china'),
                'completed' => in_array($this->status, [ParcelStatus::ARRIVED_CHINA, ParcelStatus::ARRIVED_UZB, ParcelStatus::DELIVERED]),
                'current' => $this->status === ParcelStatus::ARRIVED_CHINA,
            ],
            ParcelStatus::ARRIVED_UZB->value => [
                'label' => __('dashboard.status.arrived_uzb'),
                'completed' => in_array($this->status, [ParcelStatus::ARRIVED_UZB, ParcelStatus::DELIVERED]),
                'current' => $this->status === ParcelStatus::ARRIVED_UZB,
            ],
            ParcelStatus::DELIVERED->value => [
                'label' => __('dashboard.status.delivered'),
                'completed' => $this->status === ParcelStatus::DELIVERED,
                'current' => $this->status === ParcelStatus::DELIVERED,
            ],
        ];
    }

}
