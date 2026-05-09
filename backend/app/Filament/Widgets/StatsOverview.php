<?php

namespace App\Filament\Widgets;

use App\Models\Goat;
use App\Models\HealthRecord;
use App\Models\WeightLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Kambing', Goat::count())
                ->description('Jumlah ternak saat ini')
                ->descriptionIcon('heroicon-m-identification')
                ->color('success'),
            Stat::make('Rata-rata Berat', round(WeightLog::avg('weight') ?? 0, 2) . ' kg')
                ->description('Dari seluruh penimbangan')
                ->descriptionIcon('heroicon-m-scale')
                ->color('info'),
            Stat::make('Tindakan Kesehatan', HealthRecord::where('status', 'completed')->count())
                ->description('Selesai dilakukan')
                ->descriptionIcon('heroicon-m-heart')
                ->color('danger'),
        ];
    }
}
