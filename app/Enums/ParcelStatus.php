<?php

namespace App\Enums;

enum ParcelStatus: string
{
    case CREATED = 'created';
    case ARRIVED_CHINA = 'arrived_china';
    case IN_WAREHOUSE = 'in_warehouse'; // Omborda
    case DISPATCHED = 'dispatched'; // Yo'lga chiqdi
    case ARRIVED_UZB = 'arrived_uzb';
    case PAYMENT_RECEIVED = 'payment_received'; // To'lov qabul qilindi
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

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
            self::IN_WAREHOUSE => 'primary',
            self::DISPATCHED => 'success',
            self::ARRIVED_UZB => 'warning',
            self::PAYMENT_RECEIVED => 'success',
            self::DELIVERED => 'danger',
            self::CANCELLED => 'danger',
        };
    }
}
