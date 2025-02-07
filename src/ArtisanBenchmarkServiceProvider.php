<?php

namespace ChristophRumpel\ArtisanBenchmark;

use ChristophRumpel\ArtisanBenchmark\Console\ArtisanBenchmarkCommand;
use Illuminate\Support\ServiceProvider;

class ArtisanBenchmarkServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands(
                commands: [
                    ArtisanBenchmarkCommand::class,
                ],
            );
        }
    }
}
