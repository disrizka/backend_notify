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
        // ── Tabel chats ──────────────────────────────────────────────────────
        if (!Schema::hasTable('chats')) {
            Schema::create('chats', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->text('message')->nullable();
                $table->string('type')->default('text'); // text, image, video, file
                $table->string('file_path')->nullable();
                $table->timestamps();
            });
        }

        // ── Tabel chat_seens ─────────────────────────────────────────────────
        if (!Schema::hasTable('chat_seens')) {
            Schema::create('chat_seens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('chat_id')->constrained('chats')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->timestamp('seen_at')->useCurrent();
                $table->timestamps();

                // Satu user hanya bisa mencatat 'seen' sekali per pesan
                $table->unique(['chat_id', 'user_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus tabel anak dulu, baru tabel induk
        Schema::dropIfExists('chat_seens');
        Schema::dropIfExists('chats');
    }
};