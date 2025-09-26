<?php

namespace App\Livewire\TelegramWebApp;

use App\Enums\ParcelStatus;
use App\Models\Client;
use Illuminate\Support\Facades\App;
use Livewire\Component;

class Dashboard extends Component
{
    public ?Client $client = null;

    public $parcels = [];

    public $selectedTab = 'unpaid';

    public $selectedStatus = 'all';

    public $searchQuery = '';

    public $selectedParcels = [];

    public $selectAll = false;

    public function mount()
    {
        $uid = request('uid');

        if ($uid) {
            $this->client = Client::findByUid($uid);

            if ($this->client) {
                // Set language based on client's telegram account language
                $telegramAccount = $this->client->telegramAccount;
                if ($telegramAccount && $telegramAccount->lang) {
                    App::setLocale($telegramAccount->lang);
                }

                $this->loadParcels();
            }
        }
    }

    public function loadParcels()
    {
        if (! $this->client) {
            return;
        }

        $query = $this->client->parcels();

        // Filter by payment status (paid/unpaid tabs)
        if ($this->selectedTab === 'paid') {
            $query->where('payment_status', 'paid');
        } elseif ($this->selectedTab === 'unpaid') {
            $query->where(function ($q) {
                $q->whereNull('payment_status')
                    ->orWhere('payment_status', '!=', 'paid');
            });
        }

        // Filter by delivery status
        if ($this->selectedStatus !== 'all') {
            $statusValue = match ($this->selectedStatus) {
                'created' => ParcelStatus::CREATED->value,
                'arrived_china' => ParcelStatus::ARRIVED_CHINA->value,
                'arrived_uzb' => ParcelStatus::ARRIVED_UZB->value,
                'delivered' => ParcelStatus::DELIVERED->value,
                default => $this->selectedStatus,
            };
            $query->where('status', $statusValue);
        }

        // Search filter
        if ($this->searchQuery) {
            $query->where('track_number', 'like', '%'.$this->searchQuery.'%');
        }

        $this->parcels = $query->latest()->get();
    }

    public function setTab($tab)
    {
        $this->selectedTab = $tab;
        $this->loadParcels();
    }

    public function setStatus($status)
    {
        $this->selectedStatus = $status;
        $this->loadParcels();
    }

    public function updatedSearchQuery()
    {
        $this->loadParcels();
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedParcels = $this->parcels->pluck('id')->toArray();
        } else {
            $this->selectedParcels = [];
        }
    }

    public function getTotalWeight()
    {
        $selectedParcels = collect($this->parcels)->whereIn('id', $this->selectedParcels);

        return $selectedParcels->sum('weight');
    }

    public function getTotalCost()
    {
        $selectedParcels = collect($this->parcels)->whereIn('id', $this->selectedParcels);
        $totalUsd = $selectedParcels->sum('payment_amount_usd');
        $totalUzs = $selectedParcels->sum('payment_amount_uzs');

        // Return formatted total cost
        if ($totalUsd > 0 && $totalUzs > 0) {
            return '$'.number_format($totalUsd, 2).' + '.number_format($totalUzs, 0, '.', ' ').' UZS';
        } elseif ($totalUsd > 0) {
            return '$'.number_format($totalUsd, 2);
        } elseif ($totalUzs > 0) {
            return number_format($totalUzs, 0, '.', ' ').' UZS';
        }

        // Calculate expected cost if no payment recorded yet
        $pricing = \App\Models\PricingSetting::getActivePricing();
        if ($pricing) {
            $totalExpectedUsd = 0;
            foreach ($selectedParcels as $parcel) {
                if ($parcel->weight) {
                    $totalExpectedUsd += $pricing->calculateExpectedAmountUsd($parcel->getCalculatedWeight());
                }
            }

            return '$'.number_format($totalExpectedUsd, 2);
        }

        return '$0.00';
    }

    public function redirectTo($link)
    {
        return redirect($link);
    }

    public function render()
    {
        return view('livewire.telegram-web-app.dashboard');
    }
}
