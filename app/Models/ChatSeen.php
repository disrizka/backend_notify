<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
 
class ChatSeen extends Model
{
    protected $fillable = ['chat_id', 'user_id', 'seen_at'];
 
    protected $casts = ['seen_at' => 'datetime'];
 
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
 
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }
}