<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goat_id')->constrained()->onDelete('cascade');
            $table->string('type'); // e.g., Vaccination, Treatment, Checkup
            $table->string('title'); // e.g., Rabies Vaccine, Worm Medicine
            $table->text('description')->nullable();
            $table->date('date_recorded');
            $table->string('status')->default('completed'); // e.g., scheduled, completed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};
