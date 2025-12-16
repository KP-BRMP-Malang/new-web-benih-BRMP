<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_session_id',
        'role',
        'content',
        'metadata',
        'token_count',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public const ROLE_USER = 'user';
    public const ROLE_ASSISTANT = 'assistant';
    public const ROLE_SYSTEM = 'system';

    /**
     * Get the chat session this message belongs to.
     */
    public function chatSession(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class);
    }

    /**
     * Create a user message.
     */
    public static function createUserMessage(string $sessionId, string $content, ?array $metadata = null): self
    {
        return self::create([
            'chat_session_id' => $sessionId,
            'role' => self::ROLE_USER,
            'content' => $content,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Create an assistant message.
     */
    public static function createAssistantMessage(
        string $sessionId,
        string $content,
        ?array $metadata = null,
        ?int $tokenCount = null
    ): self {
        return self::create([
            'chat_session_id' => $sessionId,
            'role' => self::ROLE_ASSISTANT,
            'content' => $content,
            'metadata' => $metadata,
            'token_count' => $tokenCount,
        ]);
    }

    /**
     * Set metadata value.
     */
    public function setMetadataValue(string $key, mixed $value): self
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->metadata = $metadata;
        $this->save();
        
        return $this;
    }

    /**
     * Get metadata value.
     */
    public function getMetadataValue(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Scope for user messages.
     */
    public function scopeUserMessages($query)
    {
        return $query->where('role', self::ROLE_USER);
    }

    /**
     * Scope for assistant messages.
     */
    public function scopeAssistantMessages($query)
    {
        return $query->where('role', self::ROLE_ASSISTANT);
    }
}
