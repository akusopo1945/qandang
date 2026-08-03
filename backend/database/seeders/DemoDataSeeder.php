<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Goat;
use App\Models\WeightLog;
use App\Models\HealthRecord;
use App\Models\BarnProfile;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks for clean truncation
        \DB::statement('TRUNCATE TABLE weight_logs RESTART IDENTITY CASCADE');
        \DB::statement('TRUNCATE TABLE health_records RESTART IDENTITY CASCADE');
        \DB::statement('TRUNCATE TABLE goats RESTART IDENTITY CASCADE');
        \DB::statement('TRUNCATE TABLE barn_profiles RESTART IDENTITY CASCADE');

        // Create Barn Profile
        BarnProfile::create([
            'name' => 'Qandang Utama',
            'owner_name' => 'Admin Qandang',
            'phone' => '+62 822-4499-4491',
            'address' => 'Desa Sitirejo, Kecamatan Wagir, Kabupaten Malang, Indonesia',
            'village' => 'Desa Sitirejo',
            'district' => 'Kecamatan Wagir',
            'city' => 'Kabupaten Malang',
            'province' => 'Jawa Timur',
            'capacity' => 100,
            'description' => 'Kandang utama Qandang Smart Farming untuk penggemukan dan pembiakan kambing unggul.',
        ]);

        // 1. Create Goat: Jono (Male, Boer, Fattening, 1 Year old)
        $jono = Goat::create([
            'name' => 'Jono',
            'breed' => 'Boer',
            'gender' => 'male',
            'purpose' => 'fattening',
            'birth_date' => Carbon::now()->subMonths(12)->toDateString(),
            'initial_weight' => 15.0,
            'current_weight' => 52.5,
            'target_weight' => 60.0,
            'height' => 70,
            'description' => 'Kambing Boer pejantan unggul untuk penggemukan.',
            'sale_status' => 'for_sale',
            'price' => 3500000,
            'is_featured' => true,
        ]);

        // Jono's Weight Logs (Fluctuating/Growing over 6 months)
        $jonoWeights = [
            ['months_ago' => 5, 'weight' => 20.5, 'note' => 'Awal masuk kandang'],
            ['months_ago' => 4, 'weight' => 28.0, 'note' => 'Pertumbuhan cepat'],
            ['months_ago' => 3, 'weight' => 35.2, 'note' => 'Penyesuaian pakan baru'],
            ['months_ago' => 2, 'weight' => 34.0, 'note' => 'Sedikit menurun karena cuaca pancaroba'],
            ['months_ago' => 1, 'weight' => 44.5, 'note' => 'Nafsu makan membaik setelah vitamin'],
            ['months_ago' => 0, 'weight' => 52.5, 'note' => 'Kondisi sangat prima menjelang target'],
        ];
        foreach ($jonoWeights as $w) {
            WeightLog::create([
                'goat_id' => $jono->id,
                'weight' => $w['weight'],
                'date_recorded' => Carbon::now()->subMonths($w['months_ago'])->toDateString(),
                'note' => $w['note'],
            ]);
        }

        // Jono's Health Records
        HealthRecord::create([
            'goat_id' => $jono->id,
            'type' => 'Vaccination',
            'title' => 'Vaksinasi PMK',
            'description' => 'Pemberian vaksin penyakit mulut dan kuku dosis pertama.',
            'date_recorded' => Carbon::now()->subMonths(4)->toDateString(),
            'status' => 'completed',
        ]);
        HealthRecord::create([
            'goat_id' => $jono->id,
            'type' => 'Vitamin',
            'title' => 'Injeksi Vitamin B-Complex',
            'description' => 'Injeksi vitamin untuk meningkatkan nafsu makan.',
            'date_recorded' => Carbon::now()->subMonths(2)->toDateString(),
            'status' => 'completed',
        ]);

        // 2. Create Goat: Joni (Male, Etawa, Breeding, 1.5 Years old)
        $joni = Goat::create([
            'name' => 'Joni',
            'breed' => 'Etawa',
            'gender' => 'male',
            'purpose' => 'breeding',
            'birth_date' => Carbon::now()->subMonths(18)->toDateString(),
            'initial_weight' => 25.0,
            'current_weight' => 78.0,
            'target_weight' => 85.0,
            'height' => 95,
            'description' => 'Pejantan Etawa ras unggul dengan postur tinggi dan telinga panjang.',
            'sale_status' => 'for_sale',
            'price' => 5500000,
            'is_featured' => true,
        ]);

        // Joni's Weight Logs
        $joniWeights = [
            ['months_ago' => 5, 'weight' => 55.0, 'note' => 'Cek bulanan rutin'],
            ['months_ago' => 4, 'weight' => 61.5, 'note' => 'Pertumbuhan normal'],
            ['months_ago' => 3, 'weight' => 60.0, 'note' => 'Sedikit lesu setelah kawin'],
            ['months_ago' => 2, 'weight' => 68.3, 'note' => 'Nafsu makan naik'],
            ['months_ago' => 1, 'weight' => 74.0, 'note' => 'Pertumbuhan pesat'],
            ['months_ago' => 0, 'weight' => 78.0, 'note' => 'Bobot ideal pejantan'],
        ];
        foreach ($joniWeights as $w) {
            WeightLog::create([
                'goat_id' => $joni->id,
                'weight' => $w['weight'],
                'date_recorded' => Carbon::now()->subMonths($w['months_ago'])->toDateString(),
                'note' => $w['note'],
            ]);
        }

        // Joni's Health Records
        HealthRecord::create([
            'goat_id' => $joni->id,
            'type' => 'Treatment',
            'title' => 'Obat Cacing Oral',
            'description' => 'Pemberian obat cacing berkala albendazole.',
            'date_recorded' => Carbon::now()->subMonths(3)->toDateString(),
            'status' => 'completed',
        ]);

        // 3. Create Goat: Lulu (Female, Saanen, Breeding, 9 Months old)
        $lulu = Goat::create([
            'name' => 'Lulu',
            'breed' => 'Saanen',
            'gender' => 'female',
            'purpose' => 'breeding',
            'reproduction_status' => 'pregnant',
            'estimated_delivery_date' => Carbon::now()->addMonths(2)->toDateString(),
            'birth_date' => Carbon::now()->subMonths(9)->toDateString(),
            'initial_weight' => 12.0,
            'current_weight' => 38.2,
            'target_weight' => 45.0,
            'height' => 60,
            'description' => 'Indukan Saanen prospek laktasi tinggi, sedang hamil.',
            'sale_status' => 'internal',
            'price' => 4200000,
            'is_featured' => false,
        ]);

        // Lulu's Weight Logs
        $luluWeights = [
            ['months_ago' => 5, 'weight' => 18.0, 'note' => 'Bobot remaja'],
            ['months_ago' => 4, 'weight' => 22.4, 'note' => 'Pertumbuhan pra-kawin'],
            ['months_ago' => 3, 'weight' => 26.0, 'note' => 'Proses perkawinan berhasil'],
            ['months_ago' => 2, 'weight' => 29.8, 'note' => 'Kehamilan awal'],
            ['months_ago' => 1, 'weight' => 33.5, 'note' => 'Kehamilan menengah'],
            ['months_ago' => 0, 'weight' => 38.2, 'note' => 'Kehamilan akhir, bobot janin naik'],
        ];
        foreach ($luluWeights as $w) {
            WeightLog::create([
                'goat_id' => $lulu->id,
                'weight' => $w['weight'],
                'date_recorded' => Carbon::now()->subMonths($w['months_ago'])->toDateString(),
                'note' => $w['note'],
            ]);
        }

        // Lulu's Health Records
        HealthRecord::create([
            'goat_id' => $lulu->id,
            'type' => 'Vitamin',
            'title' => 'Suntik Kalsium & Vitamin AD3E',
            'description' => 'Suplementasi prenatal untuk indukan bunting.',
            'date_recorded' => Carbon::now()->subMonths(1)->toDateString(),
            'status' => 'completed',
        ]);
        HealthRecord::create([
            'goat_id' => $lulu->id,
            'type' => 'Vaccination',
            'title' => 'Vaksinasi PMK Booster',
            'description' => 'Vaksinasi penguat antibodi sebelum melahirkan.',
            'date_recorded' => Carbon::now()->subMonths(2)->toDateString(),
            'status' => 'completed',
        ]);
    }
}
