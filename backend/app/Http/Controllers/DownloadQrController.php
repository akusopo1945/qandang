<?php

namespace App\Http\Controllers;

use App\Models\Goat;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DownloadQrController extends Controller
{
    public function download($id)
    {
        $goat = Goat::findOrFail($id);
        $qrCode = $goat->qr_code ?? $goat->id;

        $image = QrCode::format('png')
            ->size(500)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($qrCode);

        return response($image)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="qr-' . $qrCode . '.png"');
    }
}
