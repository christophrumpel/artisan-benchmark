<?php

declare(strict_types=1);

namespace Tests\Commands;

use Illuminate\Console\Command;

class TestWithArgumentsCommand extends Command
{
    protected $signature = '
        with-arguments:test
        {custom-argument}
        {--custom-option}
        {--C|custom-option-with-shortcut}
        {--custom-option-with-value=}
        {--custom-option-array=*}
    ';

    public function handle(): void
    {
        $this->line('Custom argument: '.$this->argument('custom-argument'));
        $this->line('Custom option: '.$this->option('custom-option'));
        $this->line('Custom option with shortcut: '.$this->option('custom-option-with-shortcut'));
        $this->line('Custom option with value: '.$this->option('custom-option-with-value'));
        $this->line('Custom option array: '.implode(', ', $this->option('custom-option-array')));
    }
}
