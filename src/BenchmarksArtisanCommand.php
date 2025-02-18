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
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;

use function Laravel\Prompts\select;

trait BenchmarksArtisanCommand
{
    protected float $benchmarkStartTime;

    protected int $benchmarkStartMemory;

    protected int $queryCount = 0;

    protected ?int $tableToWatchBeginCount;

    public function __construct()
    {
        parent::__construct();

        $this->ignoreValidationErrors();
    }

    public function handle(): void
    {
        $commandToBenchmark = $this->argument('signature');

        if (! $commandToBenchmark) {
            $allSignatures = collect(Artisan::all())->keys();
            $commandToBenchmark = select('Which command do you want to benchmark?', $allSignatures->toArray());
        }

        $this->startBenchmark();
        $this->call($commandToBenchmark, $this->getArgumentsToPassThough());
        $this->endBenchmark();
    }

    protected function getArgumentsToPassThough(): array
    {
        $commandToBenchmark = $this->argument('signature');

        if (! $commandToBenchmark) {
            return [];
        }

        $command = Artisan::all()[$commandToBenchmark] ?? null;

        if (! $command) {
            throw new CommandNotFoundException(
                \sprintf('Command "%s" does not exist.', $commandToBenchmark)
            );
        }

        $booleanOptions = array_keys(array_filter(
            $command->getDefinition()->getOptions(),
            fn (InputOption $option) => ! $option->acceptValue(),
        ));

        $tokens = collect((new StringInput($this->input))->getRawTokens())
            ->reject(fn (string $token): string => str_starts_with($token, '--tableToWatch='))
            ->map(function (string $token) use ($booleanOptions): string {
                $optionName = Str::match('/^--([^=]+)=/', $token);

                if (in_array($optionName, $booleanOptions)) {
                    return '--'.$optionName;
                }

                return $token;
            })
            ->slice(1)
            ->toArray();

        $input = new ArgvInput($tokens, $command->getDefinition());

        return [
            ...$input->getArguments(),
            ...Arr::mapWithKeys(
                $input->getOptions(),
                fn (array|string|null $option, string $key): array => [Str::start($key, '--') => $option]
            ),
        ];
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
