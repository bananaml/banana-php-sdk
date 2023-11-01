<?php

namespace BananaDev\Tests;

use BananaDev\API;
use PHPUnit\Framework\TestCase;

class APITest extends TestCase
{
    public function testInstance(): void
    {
        // Just a smoke test to get started.
        $api = new API('some key');
        $this->assertNotNull($api);
    }
}
