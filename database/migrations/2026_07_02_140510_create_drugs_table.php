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
        Schema::create('drugs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('drug_categories')->nullOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('unit');
            $table->decimal('price', 15, 2)->default(0);
            $table->integer('min_stock')->default(0);
            $table->decimal('avg_daily_usage', 10, 2)->default(0);
            $table->integer('safety_stock')->default(0);
            $table->integer('rop')->default(0);
            $table->string('abc_class', 1)->nullable();
            $table->timestamps();
            
            $table->index('abc_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drugs');
    }
};
