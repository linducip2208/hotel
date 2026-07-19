<?php

declare(strict_types=1);

namespace App\Support;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case CreditCard = 'credit_card';
    case DebitCard = 'debit_card';
    case BankTransfer = 'bank_transfer';
    case Qris = 'qris';
    case Voucher = 'voucher';
    case ChargeToRoom = 'charge_to_room';
    case Other = 'other';

    public function isElectronic(): bool
    {
        return in_array($this, [
            self::CreditCard,
            self::DebitCard,
            self::BankTransfer,
            self::Qris,
        ], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::CreditCard => 'Credit Card',
            self::DebitCard => 'Debit Card',
            self::BankTransfer => 'Bank Transfer',
            self::Qris => 'QRIS',
            self::Voucher => 'Voucher',
            self::ChargeToRoom => 'Charge to Room',
            self::Other => 'Other',
        };
    }
}
