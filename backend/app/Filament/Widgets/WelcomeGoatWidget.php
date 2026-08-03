<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class WelcomeGoatWidget extends Widget
{
    protected static string $view = 'filament.widgets.welcome-goat-widget';

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';
    
    public function getGoatMessage(): string
    {
        $hour = (int) now()->format('H');
        
        if ($hour < 11) {
            return 'Selamat pagi, Juragan! 🌅 Kandang Qandang Utama sudah bersih dan rapi. Kambing-kambing Jono, Joni, dan Lulu siap diberi pakan segar!';
        } elseif ($hour < 15) {
            return 'Selamat siang, Juragan! ☀️ Kondisi suhu & kelembaban dipantau live. Jangan lupa untuk selalu memantau sirkulasi udara kandang!';
        } elseif ($hour < 19) {
            return 'Selamat sore, Juragan! ⛅ Waktunya pemberian pakan sore dan memeriksa rekam medis berkala kambing!';
        } else {
            return 'Selamat malam, Juragan! 🌙 Kambing-kambing sedang beristirahat. Sistem monitoring sensor IoT tetap aktif 24 jam!';
        }
    }
}
