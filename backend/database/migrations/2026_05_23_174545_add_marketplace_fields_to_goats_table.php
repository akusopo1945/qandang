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
            $table->decimal('price', 12, 2)->nullable()->after('description');
            $table->decimal('current_weight', 8, 2)->nullable()->after('initial_weight');
            $table->decimal('height', 8, 2)->nullable()->after('current_weight');
            $table->enum('sale_status', ['internal', 'for_sale', 'auction', 'sold'])->default('internal')->after('price');
            $table->boolean('is_featured')->default(false)->after('sale_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goats', function (Blueprint $table) {
            $table->dropColumn(['price', 'current_weight', 'height', 'sale_status', 'is_featured']);
        });
    }
};
