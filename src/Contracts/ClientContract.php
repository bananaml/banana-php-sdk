<?php

declare(strict_types=1);

namespace BananaDev\Contracts;

use BananaDev\ClientOptions;
use BananaDev\ClientResponse;

interface ClientContract
{
    public function warmup(): void;

    public function call(string $route, array $json = [], array $headers = [], ClientOptions $options = null): ClientResponse;
}
