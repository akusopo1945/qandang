<?php

namespace App\Filament\Resources\WeightLogResource\Pages;

use App\Filament\Resources\WeightLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWeightLog extends EditRecord
{
    protected static string $resource = WeightLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
