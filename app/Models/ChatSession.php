<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatSession extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'session_token',
        'context',
        'status',
        'last_activity_at',
    ];

    protected $casts = [
        'context' => 'array',
        'last_activity_at' => 'datetime',
    ];

    /**
     * Get the user that owns the chat session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all messages for this chat session.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get recent messages for context (last N messages).
     */
    public function recentMessages(int $limit = 10): HasMany
    {
        return $this->hasMany(ChatMessage::class)
            ->orderBy('created_at', 'desc')
            ->limit($limit);
    }

    /**
     * Find or create session by token.
     */
    public static function findOrCreateByToken(string $token, ?int $userId = null): self
    {
        return self::firstOrCreate(
            ['session_token' => $token],
            [
                'user_id' => $userId,
                'status' => 'active',
                'last_activity_at' => now(),
            ]
        );
    }

    /**
     * Update last activity timestamp.
     */
    public function updateLastActivity(): bool
    {
        $this->last_activity_at = now();
        return $this->save();
    }

    /**
     * Add context data to the session.
     */
    public function addContext(string $key, mixed $value): self
    {
        $context = $this->context ?? [];
        $context[$key] = $value;
        $this->context = $context;
        $this->save();
        
        return $this;
    }

    /**
     * Get context value by key.
     */
    public function getContext(string $key, mixed $default = null): mixed
    {
        return $this->context[$key] ?? $default;
    }

    /**
     * Scope for active sessions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
