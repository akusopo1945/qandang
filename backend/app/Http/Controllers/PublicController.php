<?php

namespace App\Http\Controllers;

use App\Models\Goat;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function index()
    {
        $featured = Goat::where('is_featured', true)
            ->whereIn('sale_status', ['for_sale', 'auction', 'sold'])
            ->take(3)
            ->get();

        $catalog = Goat::whereIn('sale_status', ['for_sale', 'auction', 'sold'])
            ->latest()
            ->take(8)
            ->get();

        return view('welcome', compact('featured', 'catalog'));
    }

    public function catalog(Request $request)
    {
        $query = Goat::whereIn('sale_status', ['for_sale', 'auction', 'sold']);

        if ($request->has('breed')) {
            $query->where('breed', $request->breed);
        }

        if ($request->has('type')) {
            $query->where('sale_status', $request->type);
        }

        $goats = $query->paginate(12);

        return view('marketplace.catalog', compact('goats'));
    }

    public function show($qr_code)
    {
        $goat = Goat::where('qr_code', $qr_code)
            ->whereIn('sale_status', ['for_sale', 'auction', 'sold'])
            ->firstOrFail();

        return view('marketplace.show', compact('goat'));
    }

    public function generatePdf($id)
    {
        $goat = Goat::with(['weightLogs', 'healthRecords', 'dam', 'sire'])->findOrFail($id);

        $qrCode = base64_encode(
            \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(120)
                ->margin(1)
                ->generate($goat->qr_code ?: "QND-{$goat->id}")
        );

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.goat', compact('goat', 'qrCode'));

        return $pdf->stream("sertifikat-kambing-{$goat->id}.pdf");
    }
}
