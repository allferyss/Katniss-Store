<?php

namespace App\Services\TopUp;

/** Hasil pemrosesan top-up dari supplier. */
final class TopUpResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $reference = null,
        public readonly ?string $message = null,
    ) {}
}
