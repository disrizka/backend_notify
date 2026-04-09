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
    Schema::table('chats', function (Blueprint $table) {
        if (!Schema::hasColumn('chats', 'is_edited'))
            $table->boolean('is_edited')->default(false)->after('is_pinned');
        
        if (!Schema::hasColumn('chats', 'file_path'))
            $table->string('file_path')->nullable()->after('type');
        
        if (!Schema::hasColumn('chats', 'parent_id'))
            $table->foreignId('parent_id')->nullable()->constrained('chats')->onDelete('set null');
        
        if (!Schema::hasColumn('chats', 'type'))
            $table->string('type')->default('text')->after('message');
    });

    if (!Schema::hasTable('chat_seens')) {
        Schema::create('chat_seens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('seen_at')->nullable();
            $table->timestamps();
            $table->unique(['chat_id', 'user_id']);
        });
    }
}

public function down(): void
{
    Schema::table('chats', function (Blueprint $table) {
        $table->dropColumn(['is_edited']);
    });
}
};
