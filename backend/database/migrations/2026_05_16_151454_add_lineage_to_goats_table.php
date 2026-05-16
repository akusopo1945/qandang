<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goats', function (Blueprint $table) {
            $table->foreignId('dam_id')->nullable()->constrained('goats')->onDelete('set null')->comment('Mother');
            $table->foreignId('sire_id')->nullable()->constrained('goats')->onDelete('set null')->comment('Father');
        });
    }

    public function down(): void
    {
        Schema::table('goats', function (Blueprint $table) {
            $table->dropForeign(['dam_id']);
            $table->dropForeign(['sire_id']);
            $table->dropColumn(['dam_id', 'sire_id']);
        });
    }
};
