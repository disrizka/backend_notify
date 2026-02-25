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
    Schema::table('divisions', function (Blueprint $table) {
        // Kita simpan status wajib foto/video untuk tiap step (1-4)
        for ($i = 1; $i <= 4; $i++) {
            $table->boolean("req_photo_$i")->default(false);
            $table->boolean("req_video_$i")->default(false);
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('divisions', function (Blueprint $table) {
            //
        });
    }
};
