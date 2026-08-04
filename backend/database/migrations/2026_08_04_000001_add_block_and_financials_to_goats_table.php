<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('goats', function (Blueprint $table) {
            $table->string('barn_block')->nullable()->after('breed');
            $table->decimal('purchase_price', 12, 2)->nullable()->after('price');
            $table->decimal('feeding_cost', 12, 2)->nullable()->after('purchase_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goats', function (Blueprint $table) {
            $table->dropColumn(['barn_block', 'purchase_price', 'feeding_cost']);
        });
    }
};
