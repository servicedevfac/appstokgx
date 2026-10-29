<?php

namespace Tests;

use App\Services\EntrepriseContext;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        EntrepriseContext::reset();
    }

    protected function tearDown(): void
    {
        EntrepriseContext::reset();
        parent::tearDown();
    }
}
