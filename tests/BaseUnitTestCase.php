<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase;

abstract class BaseUnitTestCase extends TestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }
}
