<?php

namespace App\Filament\Widgets;

use App\Models\SensorData;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BarnEnvironmentWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '5s';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $latest = SensorData::latest()->first();
        $recent = SensorData::latest()->take(6)->get()->reverse();

        $tempChart = $recent->pluck('temperature')->toArray();
        $humChart = $recent->pluck('humidity')->toArray();

        return [
            Stat::make('Suhu Kandang', ($latest?->temperature ?? '--') . '°C')
                ->description('Kondisi Real-time')
                ->descriptionIcon('heroicon-m-sun')
                ->chart(count($tempChart) ? $tempChart : [24, 25, 26, 25, 26, 27])
                ->color(($latest?->temperature ?? 26) > 30 ? 'danger' : 'success'),
            Stat::make('Kelembaban', ($latest?->humidity ?? '--') . '%')
                ->description('Kondisi Real-time')
                ->descriptionIcon('heroicon-m-cloud')
                ->chart(count($humChart) ? $humChart : [70, 72, 75, 74, 76, 75])
                ->color(($latest?->humidity ?? 75) > 80 ? 'warning' : 'info'),
        ];
    }
}
