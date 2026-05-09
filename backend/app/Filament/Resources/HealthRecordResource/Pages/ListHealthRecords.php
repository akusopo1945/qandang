<?php

namespace App\Filament\Resources\HealthRecordResource\Pages;

use App\Filament\Resources\HealthRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHealthRecords extends ListRecords
{
    protected static string $resource = HealthRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
