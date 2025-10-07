<?php

namespace App\Enums;

enum PrepaymentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => "To'lov kutilmoqda",
            self::PAID => "To'landi",
        };
    }

    public function label(): string
    {
        return $this->getLabel();
    }
}
