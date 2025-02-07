<?php

namespace Tests\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestImportCommand extends Command
{
    protected $signature = 'import:test';

    public function handle(): void
    {
        DB::table('users')->insert([
            'name' => 'Test User',
            'email' => 'test@import.com',
            'password' => bcrypt('password'),
        ]);
    }
}
