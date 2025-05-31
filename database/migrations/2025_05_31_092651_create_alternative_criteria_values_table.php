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
        Schema::create('alternative_criteria_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alternative_id')->constrained('alternatives')->onDelete('cascade');
            $table->foreignId('criterion_id')->constrained('criteria')->onDelete('cascade');
            $table->double('value'); // Nilai preferensi
            $table->timestamps();

            // Tambahkan unique constraint agar satu alternatif hanya punya satu nilai untuk satu kriteria
            $table->unique(['alternative_id', 'criterion_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alternative_criteria_values');
    }
};