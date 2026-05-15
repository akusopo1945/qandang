<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SensorUpdateDaemon extends Command
{
    protected $signature = 'app:sensor-update-daemon';
    protected $description = 'Fetch weather data every 60 seconds';

    public function handle()
    {
        $this->info('Starting sensor update daemon...');
        
        while (true) {
            $this->info('Fetching data at ' . now()->toDateTimeString());
            Artisan::call('app:fetch-weather-data');
            sleep(60);
        }
    }
}
