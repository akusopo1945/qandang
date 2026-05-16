<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use Filament\Widgets\ChartWidget;

class TemperatureChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Grafik Suhu Kandang (°C)';
    protected static ?string $pollingInterval = '10s';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $data = SensorData::latest()->take(30)->get()->reverse();

        return [
            'datasets' => [
                [
                    'label' => 'Suhu (°C)',
                    'data' => $data->pluck('temperature')->toArray(),
                    'borderColor' => '#ef4444',
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $data->pluck('created_at')->map(fn($date) => $date->format('H:i'))->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => false,
                    'suggestedMin' => 20,
                    'suggestedMax' => 35,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
