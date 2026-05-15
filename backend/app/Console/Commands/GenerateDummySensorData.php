<?php

namespace App\Console\Commands;

use App\Models\SensorData;
use Illuminate\Console\Command;

class GenerateDummySensorData extends Command
{
    protected $signature = 'app:generate-dummy-sensor-data';
    protected $description = 'Generate dummy sensor data for Malang/Wagir area';

    public function handle()
    {
        $baseTemp = 24.5;
        $baseHumidity = 75;

        // Generate 15 points of historical data
        for ($i = 15; $i >= 0; $i--) {
            SensorData::create([
                'temperature' => $baseTemp + rand(-20, 20) / 10,
                'humidity' => $baseHumidity + rand(-50, 50) / 10,
                'recorded_at' => now()->subMinutes($i * 5),
                'created_at' => now()->subMinutes($i * 5),
            ]);
        }

        $this->info('Dummy sensor data generated.');
    }
}
