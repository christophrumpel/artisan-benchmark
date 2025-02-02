<?php

namespace ChristophRumpel\ArtisanBenchmark;

trait BenchmarksCommand
{
    protected float $benchmarkStartTime;

    protected int $benchmarkStartMemory;

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

        $this->newLine();
        $this->line(sprintf(
            '⚡ <bg=blue;fg=black> TIME: %s </> <bg=green;fg=black> MEM: %sMB </>',
            $formattedTime,
            $memoryUsage,
        ));
        $this->newLine();
    }
}
