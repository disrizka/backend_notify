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
 Schema::create('divisions', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    // Nama step dinamis dari kepala
    $table->string('step_1')->default('Persiapan');
    $table->string('step_2')->default('Proses');
    $table->string('step_3')->default('Eksekusi');
    $table->string('step_4')->default('Selesai & Kendala');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('divisions');
    }
};
