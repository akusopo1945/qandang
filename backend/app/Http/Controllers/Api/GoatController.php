<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Goat;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;

class GoatController extends Controller
{
    public function index()
    {
        return Goat::with(['weightLogs', 'healthRecords'])->latest()->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'qr_code' => 'nullable|string|unique:goats,qr_code',
            'breed' => 'nullable|string',
            'barn_block' => 'nullable|string',
            'gender' => 'required|string',
            'purpose' => 'nullable|string',
            'reproduction_status' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'initial_weight' => 'nullable|numeric',
            'current_weight' => 'nullable|numeric',
            'target_weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'purchase_price' => 'nullable|numeric',
            'feeding_cost' => 'nullable|numeric',
            'description' => 'nullable|string',
            'dam_id' => 'nullable|exists:goats,id',
            'sire_id' => 'nullable|exists:goats,id',
            'image' => 'nullable|string',
        ]);

        $data = $request->all();

        if ($request->filled('image') && !str_contains($request->image, '.')) {
            $imageName = 'mobile_' . time() . '.jpg';
            $path = 'goats/' . $imageName;
            
            $manager = new ImageManager(new Driver());
            $image = $manager->read(base64_decode($request->image));
            
            if ($image->width() > 1000) {
                $image->scale(width: 1000);
            }
            
            $encoded = $image->toJpeg(75);
            Storage::disk('public')->put($path, (string) $encoded);
            $data['image'] = $path;
        }

        $goat = Goat::create($data);

        return response()->json($goat, 201);
    }

    public function update(Request $request, $id)
    {
        $goat = Goat::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'qr_code' => 'nullable|string|unique:goats,qr_code,' . $goat->id,
            'breed' => 'nullable|string',
            'barn_block' => 'nullable|string',
            'gender' => 'required|string',
            'purpose' => 'nullable|string',
            'reproduction_status' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'initial_weight' => 'nullable|numeric',
            'current_weight' => 'nullable|numeric',
            'target_weight' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'purchase_price' => 'nullable|numeric',
            'feeding_cost' => 'nullable|numeric',
            'description' => 'nullable|string',
            'dam_id' => 'nullable|exists:goats,id',
            'sire_id' => 'nullable|exists:goats,id',
            'image' => 'nullable|string',
        ]);

        $data = $request->all();

        if ($request->filled('image') && !str_contains($request->image, '.')) {
            $imageName = 'mobile_' . time() . '.jpg';
            $path = 'goats/' . $imageName;
            
            $manager = new ImageManager(new Driver());
            $image = $manager->read(base64_decode($request->image));
            
            if ($image->width() > 1000) {
                $image->scale(width: 1000);
            }
            
            $encoded = $image->toJpeg(75);
            Storage::disk('public')->put($path, (string) $encoded);
            $data['image'] = $path;
        }

        $goat->update($data);

        return response()->json($goat);
    }

    public function destroy($id)
    {
        $goat = Goat::findOrFail($id);
        $goat->delete();

        return response()->json(['message' => 'Ternak berhasil dihapus']);
    }

    public function show($idOrQr)
    {
        $query = Goat::with(['weightLogs', 'healthRecords', 'dam', 'sire']);

        if (is_numeric($idOrQr)) {
            $query->where(function ($q) use ($idOrQr) {
                $q->where('id', $idOrQr)
                  ->orWhere('qr_code', $idOrQr);
            });
        } else {
            $query->where('qr_code', $idOrQr);
        }

        $goat = $query->firstOrFail();

        // Convert image path to full URL for mobile
        if ($goat->image) {
            $goat->image_url = \Illuminate\Support\Facades\Storage::disk('public')->url($goat->image);
        }

        return response()->json($goat);
    }

    public function storeWeight(Request $request, $id)
    {
        $request->validate([
            'weight' => 'required|numeric',
            'date_recorded' => 'required|date',
        ]);

        $goat = Goat::findOrFail($id);
        $log = $goat->weightLogs()->create([
            'weight' => $request->weight,
            'date_recorded' => $request->date_recorded,
            'note' => $request->note,
        ]);

        return response()->json($log, 201);
    }

    public function storeHealth(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|string',
            'title' => 'required|string',
            'date_recorded' => 'required|date',
            'next_scheduled_date' => 'nullable|date',
            'image' => 'nullable|string', // Base64 from mobile
        ]);

        $data = [
            'type' => $request->type,
            'title' => $request->title,
            'date_recorded' => $request->date_recorded,
            'description' => $request->description,
            'status' => $request->status ?? 'completed',
            'next_scheduled_date' => $request->next_scheduled_date,
        ];

        if ($request->filled('image') && !str_contains($request->image, '.')) {
            $imageName = 'mobile_' . time() . '.jpg';
            $path = 'health-records/' . $imageName;
            
            $manager = new ImageManager(new Driver());
            $image = $manager->read(base64_decode($request->image));
            
            if ($image->width() > 1200) {
                $image->scale(width: 1200);
            }
            
            $encoded = $image->toJpeg(70);
            Storage::disk('public')->put($path, (string) $encoded);
            $data['image'] = $path;
        }

        $goat = Goat::findOrFail($id);
        $record = $goat->healthRecords()->create($data);

        return response()->json($record, 201);
    }

    public function predict($id)
    {
        $goat = Goat::with(['weightLogs' => function($q) {
            $q->orderBy('date_recorded', 'desc')->take(5);
        }])->findOrFail($id);
        
        $currentWeight = $goat->weightLogs->first()?->weight ?? $goat->initial_weight ?? 20;
        
        // Fix age calculation (absolute value to prevent negative)
        $birthDate = $goat->birth_date ? \Carbon\Carbon::parse($goat->birth_date) : now()->subYear();
        $ageMonths = max(1, (int)now()->diffInMonths($birthDate));

        // Prepare weight history for AI
        $history = $goat->weightLogs->reverse()->map(fn($l) => "{$l->date_recorded}: {$l->weight}kg")->implode(', ');

        try {
            $aiService = app(\App\Services\MimoAIService::class);
            $analysis = $aiService->predictGrowth([
                'name' => $goat->name,
                'breed' => $goat->breed ?? 'Lokal',
                'gender' => $goat->gender,
                'current_weight' => $currentWeight,
                'age_months' => $ageMonths,
                'history' => $history,
            ]);

            // Simple parser for AI response to get predicted weight number
            preg_match('/(\d+(\.\d+)?)\s*kg/', $analysis, $matches);
            $predictedWeight = isset($matches[1]) ? (float)$matches[1] : $currentWeight * 1.1;

            return response()->json([
                'analysis' => $analysis,
                'current_weight' => $currentWeight,
                'predicted_weight_next_month' => round($predictedWeight, 2),
                'confidence_score' => 0.85,
                'forecast_data' => [
                    ['month' => 'Sekarang', 'weight' => (float)$currentWeight],
                    ['month' => 'Bulan Depan', 'weight' => round($predictedWeight, 2)],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function exportCsv()
    {
        $goats = Goat::all();
        $csvFileName = 'goats_report_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'QR Code', 'Nama', 'Jenis', 'Jenis Kelamin', 'Berat Terakhir', 'Status'];

        $callback = function() use($goats, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($goats as $goat) {
                fputcsv($file, [
                    $goat->id,
                    $goat->qr_code,
                    $goat->name,
                    $goat->breed,
                    $goat->gender,
                    $goat->weightLogs()->latest()->first()?->weight ?? '-',
                    $goat->status ?? 'Sehat'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getBarnProfile()
    {
        $profile = \App\Models\BarnProfile::first();
        if (!$profile) {
            $profile = \App\Models\BarnProfile::create([
                'name' => 'Kandang Qandang',
                'owner_name' => 'Peternak Qandang',
                'address' => 'Jl. Tani Mulya No. 12',
                'capacity' => 100,
            ]);
        }
        return response()->json($profile);
    }

    public function updateBarnProfile(Request $request)
    {
        $profile = \App\Models\BarnProfile::first();
        if (!$profile) {
            $profile = new \App\Models\BarnProfile();
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'capacity' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);

        $profile->fill($request->all());
        $profile->save();

        return response()->json([
            'message' => 'Profil kandang berhasil diperbarui.',
            'profile' => $profile,
        ]);
    }
}
