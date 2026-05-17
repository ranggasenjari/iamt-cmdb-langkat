<?php

namespace App\Support\Ping;

class PingResult
{
    public function __construct(
        public readonly string $status,
        public readonly ?float $latencyMs = null,
    ) {
    }
}
