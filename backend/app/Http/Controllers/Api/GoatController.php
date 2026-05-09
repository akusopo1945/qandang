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

    public function show($idOrQr)
    {
        $goat = Goat::where('id', $idOrQr)
            ->orWhere('qr_code', $idOrQr)
            ->with(['weightLogs', 'healthRecords'])
            ->firstOrFail();

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
        ]);

        $goat = Goat::findOrFail($id);
        $record = $goat->healthRecords()->create([
            'type' => $request->type,
            'title' => $request->title,
            'date_recorded' => $request->date_recorded,
            'description' => $request->description,
            'status' => $request->status ?? 'completed',
        ]);

        return response()->json($record, 201);
    }
}
