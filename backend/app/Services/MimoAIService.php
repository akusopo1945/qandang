<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class MimoAIService
{
    protected $client;
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.mimo.key');
        $this->baseUrl = rtrim(config('services.mimo.url', 'https://token-plan-sgp.xiaomimimo.com/v1'), '/') . '/';
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
        ]);
    }

    public function predictGrowth(array $goatData)
    {
        try {
            $prompt = "Sebagai ahli peternakan pintar, analisislah data kambing berikut:\n" .
                      "Nama: {$goatData['name']}\n" .
                      "Jenis: {$goatData['breed']}\n" .
                      "JK: {$goatData['gender']}\n" .
                      "Berat Terakhir: {$goatData['current_weight']} kg\n" .
                      "Usia: {$goatData['age_months']} bulan\n\n" .
                      "Berikan prediksi berat badan untuk bulan depan dan skor kesehatan (0-100) serta saran singkat.";

            $response = $this->client->post('chat/completions', [
                'json' => [
                    'model' => 'mimo-v2.5-pro', 
                    'messages' => [
                        ['role' => 'system', 'content' => 'Anda adalah asisten AI ahli peternakan kambing (Livestock Expert). Analisis data dengan data-driven dan berikan saran praktis.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            return $result['choices'][0]['message']['content'] ?? 'Gagal mendapatkan prediksi.';
        } catch (\Exception $e) {
            Log::error('MiMo AI Error: ' . $e->getMessage());
            return 'Layanan AI sedang tidak tersedia: ' . $e->getMessage();
        }
    }
}
