<?php

namespace ChristophRumpel\ArtisanBenchmark;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\ArgvInput;

use function Laravel\Prompts\select;

trait BenchmarksArtisanCommand
{
    protected float $benchmarkStartTime;

    protected int $benchmarkStartMemory;

    protected int $queryCount = 0;

    protected ?int $tableToWatchBeginCount;

    public function handle(): void
    {
        $commandToBenchmark = array_filter(preg_split('/\s+/', $this->argument('signature')));

        if (! $commandToBenchmark) {
            $allSignatures = collect(Artisan::all())->keys();
            $commandToBenchmark = select('Which command do you want to benchmark?', $allSignatures->toArray());
        }

        $this->startBenchmark();

        Artisan::handle(
            new ArgvInput(['artisan', ...Arr::wrap($commandToBenchmark)]),
            $this->output
        );

        $this->endBenchmark();
    }

    protected function startBenchmark(): void
    {
        $this->benchmarkStartTime = microtime(true);
        $this->benchmarkStartMemory = memory_get_usage();

        if ($tableToWatch = $this->option('tableToWatch')) {
            $this->tableToWatchBeginCount = DB::table($tableToWatch)->count();
        }

        Event::listen(QueryExecuted::class, fn () => $this->queryCount++);
    }

    protected function endBenchmark(): void
    {
        $metrics = collect([
            'time' => $this->formatExecutionTime(microtime(true) - $this->benchmarkStartTime),
            'memory' => round((memory_get_usage() - $this->benchmarkStartMemory) / 1024 / 1024, 2).'MB',
            'queries' => $this->queryCount,
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
