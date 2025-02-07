<?php

namespace Tests;

use ChristophRumpel\ArtisanBenchmark\ArtisanBenchmarkServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ArtisanBenchmarkServiceProvider::class,
        ];
    }
}
