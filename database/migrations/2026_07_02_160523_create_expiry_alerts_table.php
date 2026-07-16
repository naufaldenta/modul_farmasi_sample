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
        Schema::create('expiry_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drug_batch_id')->constrained()->cascadeOnDelete();
            $table->integer('alert_level'); // 90, 30, 7, 0
            $table->string('status')->default('pending'); // pending, resolved, returned
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['drug_batch_id', 'alert_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expiry_alerts');
    }
};
