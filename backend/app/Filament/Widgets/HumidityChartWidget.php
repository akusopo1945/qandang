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
        $data = SensorData::where('created_at', '>=', now()->subHours(2))
            ->selectRaw('
                floor(extract(epoch from created_at) / 300) as timeframe,
                AVG(humidity) as avg_hum,
                MAX(created_at) as latest_time
            ')
            ->groupBy('timeframe')
            ->orderBy('timeframe', 'asc')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Kelembaban (%)',
                    'data' => $data->pluck('avg_hum')->map(fn($v) => round($v, 1))->toArray(),
                    'borderColor' => '#3b82f6',
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
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
