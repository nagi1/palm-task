<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $path = storage_path('app/product_urls.txt');
        if (is_file($path)) {
            // scrape every hour; adjust as needed
            $schedule->command('products:scrape-url --file='.$path)->hourly()->onOneServer();
        }
    }
}
