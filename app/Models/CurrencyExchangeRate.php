<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyExchangeRate extends Model
{
    protected $fillable = [
        'from_currency',
        'to_currency',
        'rate',
        'is_active',
        'effective_date',
        'notes',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'is_active' => 'boolean',
        'effective_date' => 'datetime',
    ];

    /**
     * Get the current exchange rate for USD to UZS
     */
    public static function getCurrentUsdToUzsRate(): float
    {
        $rate = self::where('from_currency', 'USD')
            ->where('to_currency', 'UZS')
            ->where('is_active', true)
            ->orderBy('effective_date', 'desc')
            ->first();

        return $rate ? (float) $rate->rate : 12500.0; // Default fallback rate
    }

    /**
     * Convert USD to UZS using current rate
     */
    public static function convertUsdToUzs(float $usdAmount): float
    {
        return $usdAmount * self::getCurrentUsdToUzsRate();
    }

    /**
     * Convert UZS to USD using current rate
     */
    public static function convertUzsToUsd(float $uzsAmount): float
    {
        $rate = self::getCurrentUsdToUzsRate();

        return $rate > 0 ? $uzsAmount / $rate : 0;
    }
}
