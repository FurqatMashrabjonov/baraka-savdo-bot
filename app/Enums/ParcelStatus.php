<?php

namespace App\Enums;

enum ParcelStatus: string
{
    case CREATED = 'created';
    case ARRIVED_CHINA = 'arrived_china';
    case IN_WAREHOUSE = 'in_warehouse'; // Omborda
    case DISPATCHED = 'dispatched'; // Yo'lga chiqdi
    case ARRIVED_UZB = 'arrived_uzb';
    case DELIVERED = 'delivered';


    public function getLabel(?string $locale = null): string
    {
        return __("telegram.parcel_statuses.{$this->value}");
    }

    public function label(): string
    {
        return $this->getLabel();
    }

    public function getColor(): string
    {
        return match ($this) {
            self::CREATED => 'gray',
            self::ARRIVED_CHINA => 'info',
            self::ARRIVED_UZB => 'warning',
            self::DELIVERED => 'success',
        };
    }
}
