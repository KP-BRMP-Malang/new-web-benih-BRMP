<?php

namespace App\DTO;

use JsonSerializable;

/**
 * Data Transfer Object for Router LLM output.
 */
class RouterOutput implements JsonSerializable
{
    public function __construct(
        public readonly string $intent,
        public readonly array $filters,
        public readonly array $sources,
        public readonly float $confidence,
        public readonly ?string $clarificationNeeded = null,
    ) {}

    /**
     * Create from array (parsed JSON).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            intent: $data['intent'] ?? 'unknown',
            filters: $data['filters'] ?? [],
            sources: $data['sources'] ?? [],
            confidence: (float) ($data['confidence'] ?? 0.0),
            clarificationNeeded: $data['clarification_needed'] ?? null,
        );
    }

    /**
     * Check if the router needs clarification.
     */
    public function needsClarification(): bool
    {
        return $this->confidence < 0.5 || !empty($this->clarificationNeeded);
    }

    /**
     * Check if intent is out of scope.
     */
    public function isOutOfScope(): bool
    {
        return $this->intent === 'out_of_scope';
    }

    /**
     * Check if this is a general chat/greeting.
     */
    public function isGeneralChat(): bool
    {
        return in_array($this->intent, ['greeting', 'thanks', 'general_chat', 'chat', 'farewell']);
    }

    public function jsonSerialize(): array
    {
        return [
            'intent' => $this->intent,
            'filters' => $this->filters,
            'sources' => $this->sources,
            'confidence' => $this->confidence,
            'clarification_needed' => $this->clarificationNeeded,
        ];
    }
}
