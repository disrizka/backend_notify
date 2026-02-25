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
    Schema::table('users', function (Blueprint $table) {
        // Tambahkan kolom division_id yang mengacu ke tabel divisions
        $table->foreignId('division_id')->nullable()->after('id')->constrained('divisions'); 
        $table->enum('role', ['kepala', 'karyawan'])->default('karyawan');
        $table->boolean('is_default_password')->default(true);
        $table->string('avatar')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
