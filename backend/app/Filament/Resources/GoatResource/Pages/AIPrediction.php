<?php

namespace App\Filament\Resources\GoatResource\Pages;

use App\Filament\Resources\GoatResource;
use App\Models\Goat;
use Filament\Resources\Pages\Page;
use App\Http\Controllers\Api\GoatController;

class AIPrediction extends Page
{
    protected static string $resource = GoatResource::class;

    protected static string $view = 'filament.resources.goat-resource.pages.a-i-prediction';

    public $goatId;
    public $analysisData;

    public function mount($record)
    {
        $this->goatId = $record;
        $controller = app(GoatController::class);
        $response = $controller->predict($this->goatId);
        $this->analysisData = json_decode($response->getContent(), true);
    }

    public function getTitle(): string
    {
        $goat = Goat::find($this->goatId);
        return "Analisis AI: " . ($goat?->name ?? 'Ternak');
    }
}
