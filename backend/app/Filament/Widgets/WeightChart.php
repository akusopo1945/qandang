<?php

namespace App\Filament\Widgets;

use App\Models\WeightLog;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Facades\DB;

class WeightChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Pertumbuhan Berat (Rata-rata)';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = DB::table('weight_logs')
            ->select(DB::raw('date_recorded, AVG(weight) as average_weight'))
            ->groupBy('date_recorded')
            ->orderBy('date_recorded', 'asc')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Berat (kg)',
                    'data' => $data->map(fn ($value) => $value->average_weight),
                    'borderColor' => '#10b981',
                ],
            ],
            'labels' => $data->map(fn ($value) => $value->date_recorded),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
