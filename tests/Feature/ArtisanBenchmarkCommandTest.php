<?php

use ChristophRumpel\ArtisanBenchmark\Console\ArtisanBenchmarkCommand;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Tests\Commands\TestImportCommand;

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
