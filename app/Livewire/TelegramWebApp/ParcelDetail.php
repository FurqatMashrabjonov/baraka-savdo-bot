<?php

namespace App\Livewire\TelegramWebApp;

use App\Models\Client;
use App\Models\Parcel;
use Illuminate\Support\Facades\App;
use Livewire\Component;

class ParcelDetail extends Component
{
    public ?Client $client = null;

    public ?Parcel $parcel = null;

    public $progressSteps = [];

    public function mount($parcelId)
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

                // Find parcel belonging to this client
                $this->parcel = $this->client->parcels()->find($parcelId);

                if ($this->parcel) {
                    $this->progressSteps = $this->parcel->getProgressSteps();
                }
            }
        }
    }

    public function goBack()
    {
        return $this->redirect('/telegram-web-app/dashboard?uid='.$this->client->uid);
    }

    public function render()
    {
        return view('livewire.telegram-web-app.parcel-detail');
    }
}
