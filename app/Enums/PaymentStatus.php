<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Belum dibayar',
            self::Pending => 'Menunggu pembayaran',
            self::Paid => 'Dibayar',
            self::Failed => 'Gagal',
            self::Refunded => 'Dikembalikan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Unpaid => 'gray',
            self::Pending => 'yellow',
            self::Paid => 'green',
            self::Failed => 'red',
            self::Refunded => 'purple',
        };
    }

    /**
     * @return array<int, self>
     */
    public function transitions(): array
    {
        return match ($this) {
            self::Unpaid => [self::Pending, self::Paid, self::Failed],
            self::Pending => [self::Paid, self::Failed],
            self::Failed => [self::Pending, self::Paid],
            self::Paid => [self::Refunded],
            self::Refunded => [],
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->transitions(), true);
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
