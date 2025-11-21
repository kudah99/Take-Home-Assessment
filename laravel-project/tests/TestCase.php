<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        return $app;
    }

    /**
     * Setup the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up any test-specific configuration
        $this->artisan('migrate', ['--database' => 'sqlite']);
    }

    /**
     * Tear down the test environment
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
