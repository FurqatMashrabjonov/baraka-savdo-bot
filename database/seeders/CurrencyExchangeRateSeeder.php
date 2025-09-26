<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CurrencyExchangeRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some test exchange rates
        $rates = [
            [
                'from_currency' => 'USD',
                'to_currency' => 'UZS',
                'rate' => 12650.0000,
                'is_active' => true,
                'effective_date' => now()->subDays(7),
                'notes' => 'Historical rate from last week',
            ],
            [
                'from_currency' => 'USD',
                'to_currency' => 'UZS',
                'rate' => 12700.0000,
                'is_active' => true,
                'effective_date' => now()->subDays(3),
                'notes' => 'Updated rate from 3 days ago',
            ],
            [
                'from_currency' => 'USD',
                'to_currency' => 'UZS',
                'rate' => 12750.0000,
                'is_active' => true,
                'effective_date' => now(),
                'notes' => 'Current exchange rate for testing',
            ],
        ];

        foreach ($rates as $rate) {
            \App\Models\CurrencyExchangeRate::create($rate);
        }
    }
}
