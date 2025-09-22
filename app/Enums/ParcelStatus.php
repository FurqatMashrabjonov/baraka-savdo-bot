<?php

namespace App\Enums;

enum ParcelStatus: string
{
    case CREATED = 'created';
    case ARRIVED_CHINA = 'arrived_china';
    case ARRIVED_UZB = 'arrived_uzb';
    case DELIVERED = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::CREATED => 'Yaratilgan',
            self::ARRIVED_CHINA => 'Xitoyga kelgan',
            self::ARRIVED_UZB => 'O\'zbekistonga kelgan',
            self::DELIVERED => 'Yetkazilgan',
        };
    }
}
