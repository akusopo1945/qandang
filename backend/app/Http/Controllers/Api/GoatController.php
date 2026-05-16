<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Goat;
use Illuminate\Http\Request;

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
            'gender' => 'required|string',
            'dam_id' => 'nullable|exists:goats,id',
            'sire_id' => 'nullable|exists:goats,id',
            'image' => 'nullable|string', // Base64 from mobile
        ]);

        $data = $request->all();

        if ($request->filled('image') && !str_contains($request->image, '/')) {
            $image = $request->image;
            $imageName = 'mobile_' . time() . '.jpg';
            \Illuminate\Support\Facades\Storage::disk('public')->put('goats/' . $imageName, base64_decode($image));
            $data['image'] = 'goats/' . $imageName;
        }

        $goat = Goat::create($data);

        return response()->json($goat, 201);
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

        if ($request->filled('image') && !str_contains($request->image, '/')) {
            $image = $request->image;
            $imageName = 'mobile_' . time() . '.jpg';
            \Illuminate\Support\Facades\Storage::disk('public')->put('health-records/' . $imageName, base64_decode($image));
            $data['image'] = 'health-records/' . $imageName;
        }

        $goat = Goat::findOrFail($id);
        $record = $goat->healthRecords()->create($data);

        return response()->json($record, 201);
    }

    public function predict($id)
    {
        $goat = Goat::with('weightLogs')->findOrFail($id);
        $currentWeight = $goat->weightLogs()->latest()->first()?->weight ?? 20;
        
        $birthDate = $goat->birth_date ? \Carbon\Carbon::parse($goat->birth_date) : now()->subYear();
        $ageMonths = $birthDate->diffInMonths(now());

        try {
            $response = \Illuminate\Support\Facades\Http::post(config('services.ai_service.url') . '/predict/growth', [
                'current_weight' => (float)$currentWeight,
                'age_months' => (int)$ageMonths,
                'breed' => $goat->breed ?? 'Jawa Randu',
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            // Fallback if AI service is down
        }

        return response()->json([
            'predicted_weight_next_month' => round($currentWeight * 1.1, 2),
            'confidence_score' => 0.7,
            'note' => 'Estimasi manual (AI Offline)'
        ]);
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
}
