<?php

namespace BananaDev\Tests;

use BananaDev\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testInstance(): void
    {
        // Just a smoke test to get started.
        $client = new Client('some key', 'http://localhost:8000');
        $this->assertNotNull($client);
    }
}
