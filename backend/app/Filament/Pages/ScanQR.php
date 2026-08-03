<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ScanQR extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'Scan QR Kambing';

    protected static ?string $title = 'Scanner QR Code';

    protected static string $view = 'filament.pages.scan-q-r';
}
