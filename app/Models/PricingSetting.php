<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingSetting extends Model
{
    /** @use HasFactory<\Database\Factories\PricingSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'price_per_kg_usd',
        'is_active',
        'updated_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'price_per_kg_usd' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function getActivePricing(): ?self
    {
        return static::where('is_active', true)->latest()->first();
    }

    public function calculateExpectedAmountUsd(float $weightKg): float
    {
        return round($this->price_per_kg_usd * $weightKg, 2);
    }

    public function calculateExpectedAmountUzs(float $weightKg): float
    {
        $usdAmount = $this->calculateExpectedAmountUsd($weightKg);

        return CurrencyExchangeRate::convertUsdToUzs($usdAmount);
    }

    public function getFormattedPriceUsdAttribute(): string
    {
        return '$'.number_format($this->price_per_kg_usd, 2);
    }

    public function getCalculationSteps(float $weightKg): array
    {
        $exchangeRate = CurrencyExchangeRate::getCurrentUsdToUzsRate();
        $expectedUsd = $this->calculateExpectedAmountUsd($weightKg);
        $expectedUzs = $this->calculateExpectedAmountUzs($weightKg);

        return [
            'weight' => $weightKg,
            'usd_per_kg' => $this->price_per_kg_usd,
            'exchange_rate' => $exchangeRate,
            'expected_usd' => $expectedUsd,
            'expected_uzs' => $expectedUzs,
            'step1' => $weightKg.' kg × $'.$this->price_per_kg_usd.' = $'.number_format($expectedUsd, 2),
            'step2' => '$'.number_format($expectedUsd, 2).' × '.number_format($exchangeRate, 0).' = '.number_format($expectedUzs, 0).' UZS',
        ];
    }
}
