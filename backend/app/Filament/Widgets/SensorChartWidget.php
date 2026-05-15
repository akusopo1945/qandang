<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use Filament\Widgets\ChartWidget;

class SensorChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Grafik Kondisi Kandang';
    protected static ?string $pollingInterval = '10s';

    protected function getData(): array
    {
        $data = SensorData::latest()->take(15)->get()->reverse();

        return [
            'datasets' => [
                [
                    'label' => 'Suhu (°C)',
                    'data' => $data->pluck('temperature')->toArray(),
                    'borderColor' => '#ef4444',
                ],
                [
                    'label' => 'Kelembaban (%)',
                    'data' => $data->pluck('humidity')->toArray(),
                    'borderColor' => '#3b82f6',
                ],
            ],
            'labels' => $data->pluck('created_at')->map(fn($date) => $date->format('H:i:s'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
