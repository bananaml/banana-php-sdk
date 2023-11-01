<?php

declare(strict_types=1);

namespace BananaDev;

class ClientResponse
{
    public function __construct(
        public readonly int $statusCode,
        public readonly array $json,
        public readonly array $headers,
    ) {
        //
    }
}
