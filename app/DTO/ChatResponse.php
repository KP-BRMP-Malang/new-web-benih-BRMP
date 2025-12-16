<?php

namespace App\DTO;

use JsonSerializable;

/**
 * Data Transfer Object for final chatbot response.
 */
class ChatResponse implements JsonSerializable
{
    public const STATUS_FOUND = 'found';
    public const STATUS_NOT_FOUND = 'not_found';
    public const STATUS_NEED_CLARIFICATION = 'need_clarification';
    public const STATUS_OUT_OF_SCOPE = 'out_of_scope';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';

    public const TYPE_PRODUCT = 'product';
    public const TYPE_ARTICLE = 'article';
    public const TYPE_FAQ = 'faq';
    public const TYPE_MIXED = 'mixed';
    public const TYPE_CHAT = 'chat';
    public const TYPE_WARNING = 'warning';

    public function __construct(
        public readonly string $status,
        public readonly string $type,
        public readonly string $message,
        public readonly array $data = [],
        public readonly ?array $metadata = null,
    ) {}

    /**
     * Create a successful response with found data.
     */
    public static function found(string $type, string $message, array $data): self
    {
        return new self(
            status: self::STATUS_FOUND,
            type: $type,
            message: $message,
            data: $data,
        );
    }

    /**
     * Create a not found response.
     */
    public static function notFound(string $message): self
    {
        return new self(
            status: self::STATUS_NOT_FOUND,
            type: self::TYPE_WARNING,
            message: $message,
            data: [],
        );
    }

    /**
     * Create a need clarification response.
     */
    public static function needClarification(string $message): self
    {
        return new self(
            status: self::STATUS_NEED_CLARIFICATION,
            type: self::TYPE_CHAT,
            message: $message,
            data: [],
        );
    }

    /**
     * Create an out of scope response.
     */
    public static function outOfScope(string $message): self
    {
        return new self(
            status: self::STATUS_OUT_OF_SCOPE,
            type: self::TYPE_WARNING,
            message: $message,
            data: [],
        );
    }

    /**
     * Create a general chat response (greetings, etc).
     */
    public static function chat(string $message): self
    {
        return new self(
            status: self::STATUS_SUCCESS,
            type: self::TYPE_CHAT,
            message: $message,
            data: [],
        );
    }

    /**
     * Create an error response.
     */
    public static function error(string $message): self
    {
        return new self(
            status: self::STATUS_ERROR,
            type: self::TYPE_WARNING,
            message: $message,
            data: [],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'status' => $this->status,
            'type' => $this->type,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }

    /**
     * Convert to array for JSON response.
     */
    public function toArray(): array
    {
        return $this->jsonSerialize();
    }
}
