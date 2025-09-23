<?php

namespace App\Livewire\TelegramWebApp;

use App\Models\Client;
use App\Models\Parcel;
use App\Enums\ParcelStatus;
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
        if (!$this->client) {
            return;
        }

        $query = $this->client->parcels();

        // Filter by status
        if ($this->selectedStatus !== 'all') {
            $statusValue = match($this->selectedStatus) {
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
            $query->where('track_number', 'like', '%' . $this->searchQuery . '%');
        }

        $this->parcels = $query->get();
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
        // Mock data - return 2.5 kg per selected parcel for testing
        return count($this->selectedParcels) * 2.5;
    }

    public function getTotalCost()
    {
        // Mock data - return 6 currency units per selected parcel for testing
        return count($this->selectedParcels) * 6;
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
