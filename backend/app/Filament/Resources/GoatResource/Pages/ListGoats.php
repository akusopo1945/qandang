<?php

namespace App\Filament\Resources\GoatResource\Pages;

use App\Filament\Resources\GoatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGoats extends ListRecords
{
    protected static string $resource = GoatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
