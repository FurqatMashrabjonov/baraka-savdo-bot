<?php

namespace App\Http\Controllers\TelegramWebApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TelegramWebAppController extends Controller
{
    public function dashboard(Request $request)
    {
        return view('telegram-web-app.dashboard');
    }

    public function parcelDetail(Request $request, $parcelId)
    {
        return view('telegram-web-app.parcel-detail', compact('parcelId'));
    }
}
