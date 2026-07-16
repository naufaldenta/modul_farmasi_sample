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
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->boolean('is_fefo_compliant')->nullable()->after('qty')->comment('Null = N/A (masuk/penyesuaian), true = FEFO dipatuhi saat keluar, false = tidak dipatuhi (ada batch lain yg lebih dekat ED)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropColumn('is_fefo_compliant');
        });
    }
};
