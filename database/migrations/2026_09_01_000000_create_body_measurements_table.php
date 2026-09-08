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
        Schema::create('body_measurements', function (Blueprint $table) {
            $table->id();
            $table->dateTime('measured_at');
            $table->decimal('weight_kg', 6, 2)->nullable();
            $table->decimal('bmi', 5, 2)->nullable();
            $table->decimal('body_fat_pct', 5, 2)->nullable();
            $table->decimal('lean_body_mass_kg', 6, 2)->nullable();
            $table->decimal('muscle_mass_kg', 6, 2)->nullable();
            $table->decimal('water_pct', 5, 2)->nullable();
            $table->decimal('protein_pct', 5, 2)->nullable();
            $table->decimal('bone_mass_kg', 6, 2)->nullable();
            $table->decimal('visceral_fat', 5, 2)->nullable();
            $table->unsignedInteger('bmr_kcal')->nullable();
            $table->unsignedTinyInteger('metabolic_age')->nullable();
            $table->decimal('impedance_ohm', 8, 2)->nullable();
            $table->string('source', 32)->nullable();
            $table->timestamps();

            $table->index('measured_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('body_measurements');
    }
};
