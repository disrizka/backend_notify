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
    Schema::table('jobs', function (Blueprint $table) {
        // Tambahkan kolom feedback setelah kolom status
        $table->text('feedback')->nullable()->after('status');
    });
}

public function down(): void
{
    Schema::table('jobs', function (Blueprint $table) {
        $table->dropColumn('feedback');
    });
}
};
