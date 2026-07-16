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
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drug_id')->constrained('drugs')->cascadeOnDelete();
            $table->foreignId('drug_batch_id')->nullable()->constrained('drug_batches')->nullOnDelete();
            $table->enum('type', ['masuk', 'keluar', 'penyesuaian']);
            $table->integer('qty');
            $table->nullableMorphs('reference');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('transaction_date');
            $table->timestamps();
            
            $table->index('transaction_date');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
