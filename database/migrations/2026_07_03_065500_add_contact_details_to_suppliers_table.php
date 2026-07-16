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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('contact_person')->nullable()->after('name');
            $table->string('phone')->nullable()->after('contact_person');
            $table->text('address')->nullable()->after('phone');
            $table->dropColumn('contact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('contact')->nullable()->after('name');
            $table->dropColumn(['contact_person', 'phone', 'address']);
        });
    }
};
