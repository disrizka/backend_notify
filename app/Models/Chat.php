<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
 
class Chat extends Model
{
    protected $fillable = [
        'user_id', 'message', 'type', 'file_path',
        'parent_id', 'is_pinned', 'is_edited',
    ];
 
    protected $casts = [
        'is_pinned' => 'boolean',
        'is_edited' => 'boolean',
    ];
 
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
 
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Chat::class, 'parent_id');
    }
 
    public function seenBy(): HasMany
    {
        return $this->hasMany(ChatSeen::class);
    }
}