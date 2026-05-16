<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use Filament\Widgets\ChartWidget;

class HumidityChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Grafik Kelembaban Kandang (%)';
    protected static ?string $pollingInterval = '10s';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $data = SensorData::latest()->take(30)->get()->reverse();

        return [
            'datasets' => [
                [
                    'label' => 'Kelembaban (%)',
                    'data' => $data->pluck('humidity')->toArray(),
                    'borderColor' => '#3b82f6',
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
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
                    'suggestedMin' => 40,
                    'suggestedMax' => 100,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
