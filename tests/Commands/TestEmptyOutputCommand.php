<?php

namespace Tests\Commands;

use Illuminate\Console\Command;

class TestEmptyOutputCommand extends Command
{
    protected $signature = 'emptyoutput:test';

    public function handle(): void {}
}
