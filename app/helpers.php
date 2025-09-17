<?php

function lang_all(?string $key = null): array
{
    return [
        __($key, locale: 'uz'),
        __($key, locale: 'ru'),
    ];
}
