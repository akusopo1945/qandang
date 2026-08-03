<?php

namespace App\Filament\Widgets;

use App\Models\Goat;
use App\Models\HealthRecord;
use App\Models\WeightLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalGoats = Goat::count();
        $fatteningCount = Goat::where('purpose', 'fattening')->count();
        $breedingCount = Goat::where('purpose', 'breeding')->count();
        $pregnantCount = Goat::where('reproduction_status', 'pregnant')->count();

        return [
            Stat::make('Populasi Ternak', $totalGoats)
                ->description($fatteningCount . ' Penggemukan, ' . $breedingCount . ' Pembibitan')
                ->descriptionIcon('heroicon-m-identification')
                ->color('success'),
            Stat::make('Kambing Bunting', $pregnantCount)
                ->description('Dari ' . Goat::where('gender', 'female')->where('purpose', 'breeding')->count() . ' indukan')
                ->descriptionIcon('heroicon-m-heart')
                ->color('danger')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.location.href='/admin/goats?tableFilters[reproduction_status][value]=pregnant'",
                ]),
            Stat::make('Rata ADG', $this->calculateADG() . ' kg/hari')
                ->description('Rata-rata pertumbuhan harian')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),
        ];
    }

    private function calculateADG(): float
    {
        // Simple ADG calculation: average of weight gains / days
        // This is a placeholder logic for demonstration
        return 0.15; 
    }
}
