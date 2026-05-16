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
        $this->info('Generating realistic dummy sensor data...');
        
        // Base values
        $avgTemp = 26;
        $tempAmp = 4; // +/- 4 degrees
        $avgHum = 75;
        $humAmp = 10; // +/- 10%
        
        // Generate 50 points of historical data (every 10 minutes)
        for ($i = 50; $i >= 0; $i--) {
            $time = now()->subMinutes($i * 10);
            $hour = (int) $time->format('H');
            $minute = (int) $time->format('i');
            
            // Daily cycle: Coldest at 4 AM, Warmest at 2 PM (14:00)
            // Using sine wave: sin((hour - 8) * PI / 12)
            $phase = ($hour + ($minute / 60) - 8) * pi() / 12;
            $sine = sin($phase);
            
            $temperature = $avgTemp + ($tempAmp * $sine) + (rand(-10, 10) / 10);
            $humidity = $avgHum - ($humAmp * $sine) + (rand(-20, 20) / 10);
            
            SensorData::create([
                'temperature' => round($temperature, 1),
                'humidity' => round($humidity, 1),
                'recorded_at' => $time,
                'created_at' => $time,
            ]);
        }

        $this->info('Realistic dummy sensor data generated.');
    }
}
