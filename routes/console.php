<?php

use Illuminate\Support\Facades\Schedule;

// Run `php artisan schedule:run` every minute from cron on the server.
Schedule::command('wayl:reconcile')->hourly()->withoutOverlapping();
Schedule::command('pro:remind')->dailyAt('10:00')->timezone('Asia/Baghdad');
