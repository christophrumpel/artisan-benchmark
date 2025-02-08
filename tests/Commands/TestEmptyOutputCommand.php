<?php

namespace Tests\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestEmptyOutputCommand extends Command
{
    protected $signature = 'emptyoutput:test';

    public function handle(): void
    {
    }
}
