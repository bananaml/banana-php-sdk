<?php

declare(strict_types=1);

namespace BananaDev\Responses;

class APIProjectResponse
{
    public function __construct(
        public readonly array $json,
        public readonly int $statusCode,
    ) {
        //
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResult(): array
    {
        return $this->json;
    }
}
