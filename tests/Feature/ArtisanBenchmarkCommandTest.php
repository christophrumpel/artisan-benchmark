<?php

use ChristophRumpel\ArtisanBenchmark\Console\ArtisanBenchmarkCommand;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Exception\CommandNotFoundException;

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

test('it can benchmark an artisan command selected from a list', function () {
    // Act & Assert
    $this->artisan(ArtisanBenchmarkCommand::class)
        ->expectsQuestion('Which command to you want to benchmark?', 'about')
        ->assertSuccessful()
        ->expectsOutputToContain('TIME:')
        ->expectsOutputToContain('MEM:')
        ->expectsOutputToContain('SQL:');
})->todo();

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

test('it handles invalid commands appropriately', function () {
    // Act
    Artisan::call('benchmark non-existent-command');
})->throws(CommandNotFoundException::class);


