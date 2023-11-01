<?php

declare(strict_types=1);

namespace BananaDev;

class ClientOptions
{
    public function __construct(
        public readonly bool $retry = true,
        public readonly int $retryTimeoutMs = 300000,
    ) {
        //
    }
}
