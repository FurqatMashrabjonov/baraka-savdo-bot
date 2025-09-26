<?php

namespace App\Models;

use App\Enums\ParcelStatus;
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
        'payment_status',
        'payment_date',
        'payment_type',
        'payment_amount_usd',
        'payment_amount_uzs',
        'exchange_rate',
        'payment_notes',
        'processed_by',
        'payment_processed_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => ParcelStatus::class,
            'is_banned' => 'boolean',
            'weight' => 'decimal:3',
            'china_uploaded_at' => 'datetime',
            'uzb_uploaded_at' => 'datetime',
            'payment_date' => 'datetime',
            'payment_amount_usd' => 'decimal:2',
            'payment_amount_uzs' => 'decimal:2',
            'exchange_rate' => 'decimal:4',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function paymentProcessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_processed_by');
    }

    /**
     * Get weight with KG suffix and ceil to .5 precision
     */
    public function getFormattedWeightAttribute(): string
    {
        if (! $this->weight) {
            return '—';
        }

        $ceiledWeight = $this->getCalculatedWeight();

        return number_format($ceiledWeight, 1).' KG';
    }

    /**
     * Get calculated weight for pricing (ceil to nearest 0.5)
     */
    public function getCalculatedWeight(): float
    {
        if (! $this->weight) {
            return 0;
        }

        // Ceil to nearest 0.5
        return ceil($this->weight * 2) / 2;
    }

    /**
     * Find or create parcel by track number
     */
    public static function findOrCreateByTrackNumber(string $trackNumber, ?int $clientId = null): self
    {
        $parcel = static::where('track_number', $trackNumber)->first();

        if (! $parcel) {
            $parcel = static::create([
                'track_number' => $trackNumber,
                'client_id' => $clientId,
                'status' => ParcelStatus::CREATED->value,
            ]);
        } elseif ($clientId && ! $parcel->client_id) {
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
        $paymentCompleted = $this->payment_status === 'paid';
        $readyForPayment = $this->isReadyForPayment();

        return [
            ParcelStatus::CREATED->value => [
                'label' => __('dashboard.status.created'),
                'completed' => true,
                'current' => $this->status === ParcelStatus::CREATED && ! $readyForPayment,
                'date' => $this->created_at,
            ],
            ParcelStatus::ARRIVED_CHINA->value => [
                'label' => __('dashboard.status.arrived_china'),
                'completed' => in_array($this->status, [ParcelStatus::ARRIVED_CHINA, ParcelStatus::ARRIVED_UZB, ParcelStatus::DELIVERED]),
                'current' => $this->status === ParcelStatus::ARRIVED_CHINA,
                'date' => $this->china_uploaded_at,
            ],
            ParcelStatus::ARRIVED_UZB->value => [
                'label' => __('dashboard.status.arrived_uzb'),
                'completed' => in_array($this->status, [ParcelStatus::ARRIVED_UZB, ParcelStatus::DELIVERED]),
                'current' => $this->status === ParcelStatus::ARRIVED_UZB && ! $readyForPayment,
                'date' => $this->uzb_uploaded_at,
            ],
            'payment' => [
                'label' => __('dashboard.payment_completed'),
                'completed' => $paymentCompleted,
                'current' => $readyForPayment && ! $paymentCompleted,
                'date' => $this->payment_date,
            ],
            ParcelStatus::DELIVERED->value => [
                'label' => __('dashboard.status.delivered'),
                'completed' => $this->status === ParcelStatus::DELIVERED,
                'current' => $this->status === ParcelStatus::DELIVERED,
                'date' => null, // Would need a delivery date field
            ],
        ];
    }

    /**
     * Check if parcel is ready for payment
     */
    public function isReadyForPayment(): bool
    {
        return $this->status === ParcelStatus::ARRIVED_UZB && $this->payment_status === 'pending';
    }

    /**
     * Process payment for the parcel
     */
    public function processPayment(float $usdAmount, float $uzsAmount, string $paymentType = 'offline', ?User $processedBy = null): bool
    {
        if (! $this->isReadyForPayment()) {
            return false;
        }

        $exchangeRate = CurrencyExchangeRate::getCurrentUsdToUzsRate();

        $this->update([
            'payment_status' => 'paid',
            'payment_date' => now(),
            'payment_type' => $paymentType,
            'payment_amount_usd' => $usdAmount,
            'payment_amount_uzs' => $uzsAmount,
            'exchange_rate' => $exchangeRate,
            'processed_by' => $processedBy?->id ?? auth()->id(),
            'payment_processed_by' => $processedBy?->id ?? auth()->id(),
        ]);

        return true;
    }

    /**
     * Get payment status badge color
     */
    public function getPaymentStatusColor(): string
    {
        return match ($this->payment_status) {
            'paid' => 'success',
            'cancelled' => 'danger',
            'pending' => 'warning',
            default => 'gray',
        };
    }

    /**
     * Get formatted payment amount
     */
    public function getFormattedPaymentAmount(): string
    {
        if (! $this->payment_amount_usd && ! $this->payment_amount_uzs) {
            return '—';
        }

        $parts = [];
        if ($this->payment_amount_usd > 0) {
            $parts[] = '$'.number_format($this->payment_amount_usd, 2);
        }
        if ($this->payment_amount_uzs > 0) {
            $parts[] = number_format($this->payment_amount_uzs, 0).' UZS';
        }

        return implode(' + ', $parts);
    }
}
