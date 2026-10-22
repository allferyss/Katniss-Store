<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Processing => 'Diproses',
            self::Completed => 'Selesai',
            self::Failed => 'Gagal',
            self::Cancelled => 'Dibatalkan',
        };
    }

    /** Kunci warna untuk komponen <x-badge>. */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Processing => 'blue',
            self::Completed => 'green',
            self::Failed => 'red',
            self::Cancelled => 'gray',
        };
    }

    /**
     * Transisi status yang diizinkan.
     *
     * @return array<int, self>
     */
    public function transitions(): array
    {
        return match ($this) {
            self::Pending => [self::Processing, self::Cancelled],
            self::Processing => [self::Completed, self::Failed, self::Cancelled],
            self::Failed => [self::Processing, self::Cancelled],
            self::Completed, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->transitions(), true);
    }

    public function isFinal(): bool
    {
        return $this->transitions() === [];
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
