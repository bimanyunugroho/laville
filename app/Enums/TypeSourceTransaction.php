<?php

namespace App\Enums;

enum TypeSourceTransaction: string
{
    case SHOPEE = 'SHOPEE';
    case TOKOPEDIA = 'TOKOPEDIA';
    case OFFLINE = 'OFFLINE';
    case INSTAGRAM = 'INSTAGRAM';
    case WHATSAPP = 'WHATSAPP';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::SHOPEE => 'SHOPEE',
            self::TOKOPEDIA => 'TOKOPEDIA',
            self::OFFLINE => 'OFFLINE',
            self::INSTAGRAM => 'INSTAGRAM',
            self::WHATSAPP => 'WHATSAPP'
        };
    }
}
