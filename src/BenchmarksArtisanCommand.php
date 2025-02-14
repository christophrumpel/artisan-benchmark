<?php

namespace ChristophRumpel\ArtisanBenchmark;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Number;
use Illuminate\Support\Str;
use function Laravel\Prompts\select;

trait BenchmarksArtisanCommand
{
    protected float $benchmarkStartTime;

    protected int $benchmarkStartMemory;

    protected ?int $tableToWatchBeginCount;

    public function handle(): void
    {
        $commandToBenchmark = $this->argument('signature');

        if (! $commandToBenchmark) {
            $allSignatures = collect(Artisan::all())->keys();
            $commandToBenchmark = select('Which command do you want to benchmark?', $allSignatures->toArray());
        }

        $this->startBenchmark();
        $this->call($commandToBenchmark);
        $this->endBenchmark();
    }

    protected function startBenchmark(): void
    {
        $this->benchmarkStartTime = microtime(true);
        $this->benchmarkStartMemory = memory_get_usage();

        if ($tableToWatch = $this->option('tableToWatch')) {
            $this->tableToWatchBeginCount = DB::table($tableToWatch)->count();
        }

        DB::enableQueryLog();
        DB::flushQueryLog();
    }

    protected function endBenchmark(): void
    {
        $metrics = collect([
            'time' => $this->formatExecutionTime(microtime(true) - $this->benchmarkStartTime),
            'memory' => round((memory_get_usage() - $this->benchmarkStartMemory) / 1024 / 1024, 2).'MB',
            'queries' => count(DB::getQueryLog()),
        ]);

        if ($tableToWatch = $this->option('tableToWatch')) {
            $difference = DB::table($tableToWatch)->count() - $this->tableToWatchBeginCount;
            $metrics->put(
                'rows',
                $difference > 0
                    ? Str::start(Number::format($difference), '+')
                    : Number::format($difference)
            );
        }

        $this->renderBenchmarkResults($metrics);
    }

    private function formatExecutionTime(float $executionTime): string
    {
        return match (true) {
            $executionTime >= 60 => sprintf('%dm %ds', floor($executionTime / 60), $executionTime % 60),
            $executionTime >= 1 => round($executionTime, 2).'s',
            default => round($executionTime * 1000).'ms',
        };
    }

    private function renderBenchmarkResults(Collection $metrics): void
    {
        $output = $metrics->map(fn ($value, $key) => match ($key) {
            'time' => "<bg=blue;fg=black> TIME: {$value} </>",
            'memory' => "<bg=green;fg=black> MEM: {$value} </>",
            'queries' => "<bg=yellow;fg=black> SQL: {$value} </>",
            'rows' => "<bg=magenta;fg=black> ROWS: {$value} </>",
        });

        $this->newLine();
        $this->line('⚡ '.$output->join(' '));
        $this->newLine();
    }
}
