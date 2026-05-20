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
    public $analysisData = null;
    public bool $isLoaded = false;

    public function mount($record)
    {
        $this->goatId = $record;
    }

    public function loadAnalysis()
    {
        try {
            $controller = app(GoatController::class);
            $response = $controller->predict($this->goatId);
            $this->analysisData = json_decode($response->getContent(), true);
        } catch (\Exception $e) {
            $this->analysisData = ['error' => 'Gagal terhubung ke layanan AI.'];
        }
        $this->isLoaded = true;
    }

    public function getTitle(): string
    {
        $goat = Goat::find($this->goatId);
        return "Analisis AI: " . ($goat?->name ?? 'Ternak');
    }
}
