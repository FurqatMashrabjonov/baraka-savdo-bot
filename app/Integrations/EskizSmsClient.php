<?php

namespace App\Integrations;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class EskizSmsClient
{
    private string $token;

    private string $baseUrl;

    private string $email;

    private string $password;

    /**
     * @throws ConnectionException
     */
    public function __construct()
    {
        $this->baseUrl = config('eskiz.api_url');
        $this->email = config('eskiz.email');
        $this->password = config('eskiz.password');

        $this->token = $this->getToken();
    }

    /**
     * Token olish va cache qilish
     *
     * @throws ConnectionException
     */
    private function getToken(): string
    {
        return Cache::remember('eskiz_token', config('eskiz.token_lifetime'), function () {
            $response = Http::post($this->baseUrl.'/auth/login', [
                'email' => $this->email,
                'password' => $this->password,
            ])->throw();

            return $response->object()->data->token;
        });
    }

    /**
     * SMS yuborish
     */
    public function send(string $number, int $code): array
    {
        return $this->request('/message/sms/send', 'post', [
            'mobile_phone' => $number,
            'message' => $this->generateMessage($code),
            'from' => config('eskiz.from'),
        ]);
    }

    private function generateMessage(int $code): string
    {
        return str_replace('%d', (string) $code, config('eskiz.eskiz_template'));
    }

    private function normalizeMessage(string $message): string
    {
        return $this->request('/message/sms/normalizer', 'post', [
            'message' => $message,
        ])['message'];
    }

    /**
     * Account haqida ma’lumot
     */
    public function about(): array
    {
        return $this->request('/auth/user');
    }

    /**
     * Limitlar haqida ma’lumot
     */
    public function limits(): array
    {
        return $this->request('/user/get-limit');
    }

    /**
     * Universal request method
     */
    private function request(string $endpoint, string $method = 'get', array $data = []): array
    {
        $response = Http::withToken($this->token)
            ->asForm()
            ->baseUrl($this->baseUrl)
            ->$method($endpoint, $data);

        return $response->json();
    }
}
