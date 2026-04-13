<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('office_settings', function (Blueprint $table) {
            // Tambah kolom radius_enforced
            // Default true → radius wajib (perilaku lama tetap terjaga)
            $table->boolean('radius_enforced')->default(true)->after('late_tolerance');
        });
    }

    public function down(): void
    {
        Schema::table('office_settings', function (Blueprint $table) {
            $table->dropColumn('radius_enforced');
        });
    }
};