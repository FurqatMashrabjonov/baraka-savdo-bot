<?php

return [
    'email' => env('ESKIZ_EMAIL', null),
    'password' => env('ESKIZ_PASSWORD', null),
    'from' => env('ESKIZ_FROM', 4546),
    'token_lifetime' => env('ESKIZ_TOKEN_LIFETIME', 3600), // seconds
    'api_url' => env('ESKIZ_API_URL', 'https://notify.eskiz.uz/api'),
    'test_mode' => env('ESKIZ_TEST_MODE', true),
    'eskiz_template' => env('ESKIZ_TEMPLATE', '- Baraka pochta xizmati uchun tizimga kirish tasdiqlash kodi: %d'), // Eskiz shablon ID si (agar kerak bo'lsa)
];
