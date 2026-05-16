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
            $prompt = "Analisis SINGKAT & TO THE POINT untuk kambing Qandang:\n\n" .
                      "DATA:\n" .
                      "- Nama: {$goatData['name']} ({$goatData['breed']})\n" .
                      "- Berat: {$goatData['current_weight']} kg, Usia: {$goatData['age_months']} bln\n" .
                      "- Riwayat: " . ($goatData['history'] ?: 'Baru') . "\n\n" .
                      "FORMAT RESPON (WAJIB POIN-POIN):\n" .
                      "1. KONDISI: [1 kalimat analisis]\n" .
                      "2. PREDIKSI BERAT: [Angka kg] (bulan depan)\n" .
                      "3. SKOR KESEHATAN: [0-100]\n" .
                      "4. SARAN UTAMA: [1-2 poin tindakan paling mendesak]";

            $response = $this->client->post('chat/completions', [
                'json' => [
                    'model' => 'mimo-v2.5-pro', 
                    'messages' => [
                        ['role' => 'system', 'content' => 'Anda adalah AI asisten peternak yang sangat praktis. Jangan gunakan kata-kata pembuka/penutup. Langsung ke format yang diminta.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.2,
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
