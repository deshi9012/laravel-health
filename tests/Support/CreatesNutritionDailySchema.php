<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesNutritionDailySchema
{
    protected function createNutritionDailyTable(): void
    {
        if (Schema::hasTable('nutrition_daily')) {
            return;
        }

        Schema::create('nutrition_daily', function (Blueprint $table): void {
            $table->id();
            $table->date('date')->unique();
            $table->decimal('calories_kcal', 8, 2)->nullable();
            $table->decimal('protein_g', 8, 2)->nullable();
            $table->decimal('carbs_g', 8, 2)->nullable();
            $table->decimal('fat_g', 8, 2)->nullable();
            $table->decimal('fiber_g', 8, 2)->nullable();
            $table->decimal('sugar_g', 8, 2)->nullable();
            $table->string('source', 32)->nullable();
            $table->timestamp('synced_at')->nullable();
        });
    }
}
