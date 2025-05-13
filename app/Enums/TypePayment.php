<?php

namespace App\Enums;

enum TypePayment: string
{
    case CASH = 'CASH';
    case DEBIT = 'DEBIT';
    case CREDIT = 'CREDIT';
    case WALLET = 'WALLET';
    case TRANSFER = 'TRANSFER';
    case MARKETPLACE = 'MARKETPLACE';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::CASH => 'CASH',
            self::DEBIT => 'DEBIT',
            self::CREDIT => 'CREDIT',
            self::WALLET => 'WALLET',
            self::TRANSFER => 'TRANSFER',
            self::MARKETPLACE => 'MARKETPLACE',
        };
    }
}
