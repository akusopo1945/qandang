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
            
            // Get humidity from hourly data (closest to current time)
            $humidity = $data['hourly']['relative_humidity_2m'][0] ?? 70; 

            SensorData::create([
                'temperature' => $temp,
                'humidity' => $humidity,
                'recorded_at' => now(),
            ]);

            $this->info("Successfully fetched weather: {$temp}°C, {$humidity}%");
        } else {
            $this->error("Failed to fetch weather data");
        }
    }
}
