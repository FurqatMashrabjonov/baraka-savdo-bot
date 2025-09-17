<?php

namespace App\Integrations;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class EskizSmsClient
{
    private string $token;
    private string $baseUrl = 'https://notify.eskiz.uz/api/';

    /**
     * @throws ConnectionException
     */
    public function __construct(
        private readonly string $email,
        private readonly string $password,
        private readonly string $sender,
        private readonly int    $tokenLifetime = 3600, // 1 soat default
    ) {
        $this->token = $this->getToken();
    }

    /**
     * Token olish va cache qilish
     *
     * @throws ConnectionException
     */
    private function getToken(): string
    {
        return Cache::remember('eskiz_token', $this->tokenLifetime, function () {
            $response = Http::post($this->baseUrl . 'auth/login', [
                'email'    => $this->email,
                'password' => $this->password,
            ])->throw();

            return $response->object()->data->token;
        });
    }

    /**
     * SMS yuborish
     */
    public function send(string $number, string $text): array
    {
        return $this->request('message/sms/send', 'post', [
            'mobile_phone' => $number,
            'message'      => $text,
            'from'         => $this->sender,
        ]);
    }

    /**
     * Account haqida ma’lumot
     */
    public function about(): array
    {
        return $this->request('auth/user');
    }

    /**
     * Limitlar haqida ma’lumot
     */
    public function limits(): array
    {
        return $this->request('user/get-limit');
    }

    /**
     * Universal request method
     */
    private function request(string $endpoint, string $method = 'get', array $data = []): array
    {
        $response = Http::withToken($this->token)
            ->baseUrl($this->baseUrl)
            ->$method($endpoint, $data);

        return $response->json();
    }
}
