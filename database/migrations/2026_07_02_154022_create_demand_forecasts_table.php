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
        Schema::create('demand_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drug_id')->constrained()->cascadeOnDelete();
            $table->date('period_date'); // Represents the period (e.g., first day of the month)
            $table->string('method')->default('sma'); // sma, wma, linear
            $table->decimal('predicted_qty', 10, 2);
            $table->integer('actual_qty')->nullable(); // filled after period ends
            $table->decimal('mae', 10, 2)->nullable();
            $table->decimal('mape', 10, 2)->nullable();
            $table->timestamps();

            $table->unique(['drug_id', 'period_date', 'method']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demand_forecasts');
    }
};
