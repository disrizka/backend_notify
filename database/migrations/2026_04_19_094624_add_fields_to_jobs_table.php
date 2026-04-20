<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            // Informasi klien
            $table->string('client_name')->nullable()->after('description');

            // Lokasi pekerjaan
            $table->text('location')->nullable()->after('client_name');
            $table->decimal('latitude', 10, 7)->nullable()->after('location');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');

            // Estimasi waktu (dari CS)
            $table->dateTime('start_time')->nullable()->after('longitude');
            $table->dateTime('end_time')->nullable()->after('start_time');

            // Tracking waktu aktual (dari teknisi)
            $table->dateTime('accepted_at')->nullable()->after('end_time');
            $table->dateTime('completed_at')->nullable()->after('accepted_at');
            $table->integer('actual_duration')->nullable()->after('completed_at'); // dalam menit

            // Alasan selesai / terlambat
            $table->text('completion_reason')->nullable()->after('actual_duration');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn([
                'client_name',
                'location',
                'latitude',
                'longitude',
                'start_time',
                'end_time',
                'accepted_at',
                'completed_at',
                'actual_duration',
                'completion_reason',
            ]);
        });
    }
};