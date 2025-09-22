<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Otp;
use App\Models\TelegramAccount;

class TelegramAuthService
{
    /**
     * Generate and send OTP for phone verification
     */
    public function sendOtp(string $phone): string
    {
        $otp = rand(1000, 9999);

        Otp::query()->create([
            'phone' => $phone,
            'code' => $otp,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Return the OTP code so it can be sent via Telegram
        return (string) $otp;
    }

    /**
     * Verify OTP code for a phone number
     */
    public function verifyOtp(string $phone, string $code): bool
    {
        if (! preg_match('/^\d{4}$/', $code)) {
            return false;
        }

        $otpEntry = Otp::query()
            ->where('code', $code)
            ->where('phone', $phone)
            ->where('expires_at', '>', now())
            ->first();

        if ($otpEntry) {
            $otpEntry->delete();

            return true;
        }

        return false;
    }

    /**
     * Find or create client by phone
     */
    public function getOrCreateClient(string $phone): Client
    {
        return Client::firstOrCreate(['phone' => $phone]);
    }

    /**
     * Link telegram account to client
     */
    public function linkTelegramAccountToClient(string $chatId, Client $client): TelegramAccount
    {
        $telegramAccount = TelegramAccount::findByChatId($chatId);

        if ($telegramAccount) {
            $telegramAccount->update(['client_id' => $client->id]);
        }

        return $telegramAccount;
    }

    /**
     * Get client by chat ID
     */
    public function getClientByChatId(string $chatId): ?Client
    {
        return Client::findByChatId($chatId);
    }

    /**
     * Get telegram account by chat ID
     */
    public function getTelegramAccountByChatId(string $chatId): ?TelegramAccount
    {
        return TelegramAccount::findByChatId($chatId);
    }

    /**
     * Check if phone format is valid
     */
    public function isValidPhoneFormat(string $phone): bool
    {
        return preg_match('/^\d{9}$/', $phone);
    }

    /**
     * Check if full name format is valid
     */
    public function isValidFullNameFormat(string $fullName): bool
    {
        return preg_match('/^[a-zA-ZА-яёЁ\s]+$/u', $fullName);
    }

    /**
     * Unlink telegram account from client (logout)
     */
    public function logoutTelegramAccount(string $chatId): bool
    {
        $telegramAccount = TelegramAccount::findByChatId($chatId);

        if ($telegramAccount) {
            $telegramAccount->update(['client_id' => null]);

            return true;
        }

        return false;
    }
}
