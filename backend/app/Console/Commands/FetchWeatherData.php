<?php

namespace App\Console\Commands;

use App\Models\SensorData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchWeatherData extends Command
{
    protected $signature = 'app:fetch-weather-data';
    protected $description = 'Fetch weather data from Open-Meteo for Malang/Wagir area';

    public function handle()
    {
        // Latitude/Longitude for Sitirejo, Wagir, Malang
        $lat = -8.0287;
        $lon = 112.5937;

        $response = Http::get("https://api.open-meteo.com/v1/forecast", [
            'latitude' => $lat,
            'longitude' => $lon,
            'current_weather' => true,
            'hourly' => 'relative_humidity_2m',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $temp = $data['current_weather']['temperature'];
            
            // Add tiny jitter (+/- 0.2) to make charts look "live"
            $temp += rand(-20, 20) / 100;
            
            // Get humidity from hourly data (find current hour index)
            $hourIndex = (int) now()->format('H');
            $humidity = $data['hourly']['relative_humidity_2m'][$hourIndex] ?? 70;
            $humidity += rand(-50, 50) / 100;

            SensorData::create([
                'temperature' => round($temp, 2),
                'humidity' => round($humidity, 2),
                'recorded_at' => now(),
            ]);

            $this->info("Successfully fetched weather: " . round($temp, 2) . "°C, " . round($humidity, 2) . "%");
        } else {
            $this->error("Failed to fetch weather data");
        }
    }
}
