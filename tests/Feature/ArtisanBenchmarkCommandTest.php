<?php

use ChristophRumpel\ArtisanBenchmark\Console\ArtisanBenchmarkCommand;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\Commands\TestImportCommand;
use Tests\Commands\TestWithArgumentsCommand;

test('it can benchmark an artisan command with explicit signature', function () {
    // Act
    Artisan::call('benchmark about');
    $output = Artisan::output();

    // Assert
    expect($output)
        ->toContain('TIME:')
        ->toContain('MEM:')
        ->toContain('SQL: 0');
});

test('it can benchmark an artisan command with arguments passed through', function () {
    // Arrange
    app(Kernel::class)->registerCommand(new TestWithArgumentsCommand);

    // Act
    Artisan::call(
        ArtisanBenchmarkCommand::class,
        [
            'signature' => 'with-arguments:test',
            'custom-argument' => true,
            '--custom-option' => true,
            '--custom-option-with-shortcut' => true,
            '--custom-option-with-value' => 1,
            '--custom-option-array' => [1, 2],
        ]
    );

    $output = Artisan::output();

    // Assert
    expect($output)
        ->toContain('Custom argument: 1')
        ->toContain('Custom option: 1')
        ->toContain('Custom option with shortcut: 1')
        ->toContain('Custom option with value: 1')
        ->toContain('Custom option array: 1, 2');
});

test('it can benchmark an artisan command with arguments passed through as string', function () {
    // Arrange
    app(Kernel::class)->registerCommand(new TestWithArgumentsCommand);

    $input = new StringInput('
        benchmark
        with-arguments:test
        1
        --custom-option
        -C
        --custom-option-with-value=1
        --custom-option-array=1
        --custom-option-array=2
    ');

    // Act
    Artisan::handle($input, $output = new BufferedOutput);

    // Assert
    expect($output->fetch())
        ->toContain('Custom argument: 1')
        ->toContain('Custom option: 1')
        ->toContain('Custom option with shortcut: 1')
        ->toContain('Custom option with value: 1')
        ->toContain('Custom option array: 1, 2');
});

test('it can benchmark an artisan command with arguments mixed with tableToWatch option', function () {
    // Arrange
    $this->loadLaravelMigrations();

    app(Kernel::class)->registerCommand(new TestWithArgumentsCommand);

    $input = new StringInput('
        benchmark
        --tableToWatch=users
        with-arguments:test
        1
        --custom-option
    ');

    // Act
    Artisan::handle($input, $output = new BufferedOutput);

    // Assert
    expect($output->fetch())
        ->toContain('Custom argument: 1')
        ->toContain('Custom option: 1')
        ->toContain('ROWS: 0');
});

test('it asks for command selected from a list', function () {
    // Act & Assert
    $this->artisan(ArtisanBenchmarkCommand::class)
        ->expectsQuestion('Which command do you want to benchmark?', 'about')
        ->assertSuccessful();
});

test('it shows correct SQL query count', function () {
    // Act
    Artisan::call('benchmark migrate:status');
    $output = Artisan::output();

    // Assert
    expect($output)
        ->toContain('TIME:')
        ->toContain('MEM:')
        ->toContain('SQL: 1');
});

test('it shows table to watch count has not changed', function () {
    // Arrange
    $this->loadLaravelMigrations();

    // Act
    Artisan::call('benchmark about --tableToWatch=users');
    $output = Artisan::output();

    // Assert
    expect($output)
        ->toContain('TIME:')
        ->toContain('MEM:')
        ->toContain('SQL:')
        ->toContain('USERS: 0 (same)');
})->todo();

test('it shows table to watch count has increased', function () {
    // Arrange
    $this->loadLaravelMigrations();

    // Act
    Artisan::call(TestImportCommand::class);
    $output = Artisan::output();

    // Assert
    expect($output)
        ->toContain('TIME:')
        ->toContain('MEM:')
        ->toContain('SQL:')
        ->toContain('USERS: +1');
})->todo();

test('it handles invalid commands appropriately', function () {
    // Act
    Artisan::call('benchmark non-existent-command');
})->throws(CommandNotFoundException::class);
