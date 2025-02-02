<?php

namespace ChristophRumpel\ArtisanBenchmark;

use Illuminate\Support\Facades\DB;

trait BenchmarksCommand
{
    protected float $benchmarkStartTime;

    protected int $benchmarkStartMemory;

    protected ?string $tableToBenchmark = null;

    public function handle(): void
    {
        $this->startBenchmark();
        $this->handleWithBenchmark();
        $this->endBenchmark();
    }

    protected function startBenchmark(): void
    {
        $this->benchmarkStartTime = microtime(true);
        $this->benchmarkStartMemory = memory_get_usage();
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

        $laravelQueries = count(DB::getQueryLog());

        $this->newLine();

        if ($this->tableToBenchmark) {
            $dbCount = DB::table($this->tableToBenchmark)->count();

            $this->line(sprintf(
                '⚡ <bg=blue;fg=black> TIME: %s </> <bg=green;fg=black> MEM: %sMB </> <bg=yellow;fg=black> SQL: 1 </> <bg=magenta;fg=black> ROWS: %s </>',
                $formattedTime,
                $memoryUsage,
//                $laravelQueries,
                $dbCount
            ));
            $this->newLine();
        } else {
            $this->line(sprintf(
                '⚡ <bg=blue;fg=black> TIME: %s </> <bg=green;fg=black> MEM: %sMB </> <bg=yellow;fg=black> SQL: 1 </>',
                $formattedTime,
                $memoryUsage,
                $laravelQueries
            ));
            $this->newLine();
        }


    }
}
