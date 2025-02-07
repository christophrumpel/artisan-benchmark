<?php

namespace ChristophRumpel\ArtisanBenchmark;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\select;

trait BenchmarksArtisanCommand
{
    protected float $benchmarkStartTime;

    protected int $benchmarkStartMemory;

    protected ?string $tableToBenchmark = null;

    public function handle(): void
    {
        if (! $this->argument('signature')) {
            $allSignatures = collect(Artisan::all())->keys();

            $commandToBenchmark = select('Which command to you want to benchmark?', $allSignatures->toArray());
            $this->startBenchmark();
            $this->call($commandToBenchmark);
            $this->endBenchmark();

            return;
        }
        $this->startBenchmark();
        $this->handleWithBenchmark();
        $this->endBenchmark();
    }

    protected function startBenchmark(): void
    {
        $this->benchmarkStartTime = microtime(true);
        $this->benchmarkStartMemory = memory_get_usage();

        // Enable query logging
        DB::enableQueryLog();

        // Clear any existing logs
        DB::flushQueryLog();
    }

    protected function endBenchmark(string $table = 'customers'): void
    {
        $executionTime = microtime(true) - $this->benchmarkStartTime;
        $memoryUsage = round((memory_get_usage() - $this->benchmarkStartMemory) / 1024 / 1024, 2);

        $formattedTime = match (true) {
            $executionTime >= 60 => sprintf('%dm %ds', floor($executionTime / 60), $executionTime % 60),
            $executionTime >= 1 => round($executionTime, 2).'s',
            default => round($executionTime * 1000).'ms',
        };

        // Count the queries from the log
        $laravelQueries = count(DB::getQueryLog());

        $this->newLine();

        if ($this->tableToBenchmark) {
            $dbCount = DB::table($this->tableToBenchmark)->count();

            $this->line(sprintf(
                '⚡ <bg=blue;fg=black> TIME: %s </> <bg=green;fg=black> MEM: %sMB </> <bg=yellow;fg=black> SQL: %s </> <bg=magenta;fg=black> ROWS: %s </>',
                $formattedTime,
                $memoryUsage,
                $laravelQueries,
                $dbCount
            ));
            $this->newLine();
        } else {
            $this->line(sprintf(
                '⚡ <bg=blue;fg=black> TIME: %s </> <bg=green;fg=black> MEM: %sMB </> <bg=yellow;fg=black> SQL: %s </>',
                $formattedTime,
                $memoryUsage,
                $laravelQueries
            ));
            $this->newLine();
        }
    }
}
