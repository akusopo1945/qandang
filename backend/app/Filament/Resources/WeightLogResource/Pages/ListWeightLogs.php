<?php

namespace App\Filament\Resources\WeightLogResource\Pages;

use App\Filament\Resources\WeightLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWeightLogs extends ListRecords
{
    protected static string $resource = WeightLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
