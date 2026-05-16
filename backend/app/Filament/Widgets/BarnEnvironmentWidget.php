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

        return [
            Stat::make('Suhu Kandang', ($latest?->temperature ?? '--') . '°C')
                ->description('Kondisi Real-time')
                ->descriptionIcon('heroicon-m-sun')
                ->color($latest?->temperature > 30 ? 'danger' : 'success'),
            Stat::make('Kelembaban', ($latest?->humidity ?? '--') . '%')
                ->description('Kondisi Real-time')
                ->descriptionIcon('heroicon-m-cloud')
                ->color($latest?->humidity > 80 ? 'warning' : 'info'),
        ];
    }
}
