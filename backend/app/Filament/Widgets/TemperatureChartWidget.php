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
        $data = SensorData::where('created_at', '>=', now()->subHours(2))
            ->selectRaw('
                floor(extract(epoch from created_at) / 300) as timeframe,
                AVG(temperature) as avg_temp,
                MAX(created_at) as latest_time
            ')
            ->groupBy('timeframe')
            ->orderBy('timeframe', 'asc')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Suhu (°C)',
                    'data' => $data->pluck('avg_temp')->map(fn($v) => round($v, 1))->toArray(),
                    'borderColor' => '#ef4444',
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $data->pluck('latest_time')->map(fn($date) => \Carbon\Carbon::parse($date)->format('H:i'))->toArray(),
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
