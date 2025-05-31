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
        Schema::create('criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('decision_case_id')->constrained('decision_cases')->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['benefit', 'cost']); // 'benefit' or 'cost'
            $table->double('weight'); // Bobot kriteria
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('criteria');
    }
};