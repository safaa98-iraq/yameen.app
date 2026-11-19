<?php

namespace App\Console\Commands;

use App\Http\Controllers\DemoController;
use Illuminate\Console\Command;

/** Create (or reset) the demo accounts: admin, free, monthly and lifetime Pro. */
class DemoAccounts extends Command
{
    protected $signature = 'demo:accounts';

    protected $description = 'Create or reset the demo accounts';

    public function handle(): int
    {
        foreach (array_keys(DemoController::ACCOUNTS) as $role) {
            $this->line(str_pad($role, 10).DemoController::prepare($role)->email);
        }
        if (! config('yameen.demo')) {
            $this->warn('One-click sign-in at /demo needs DEMO_MODE=true.');
        }

        return self::SUCCESS;
    }
}
