<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        // Refuse database-resetting tests if local cached configuration leaks into the suite.
        if (! $app->environment('testing') || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:') {
            throw new RuntimeException('Tests require the isolated SQLite in-memory configuration.');
        }

        return $app;
    }
}
