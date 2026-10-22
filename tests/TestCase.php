<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Test tidak membutuhkan hasil build Vite (npm run build).
        $this->withoutVite();
    }
}
