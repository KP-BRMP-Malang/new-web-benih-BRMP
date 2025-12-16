<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception for LLM-related errors.
 */
class LlmException extends Exception
{
    protected array $context;

    public function __construct(
        string $message = "LLM error occurred",
        int $code = 0,
        ?\Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Create exception for API error.
     */
    public static function apiError(string $message, ?array $response = null): self
    {
        return new self(
            "LLM API Error: {$message}",
            500,
            null,
            ['response' => $response]
        );
    }

    /**
     * Create exception for invalid JSON response.
     */
    public static function invalidJson(string $content): self
    {
        return new self(
            "LLM returned invalid JSON",
            422,
            null,
            ['content' => $content]
        );
    }

    /**
     * Create exception for rate limiting.
     */
    public static function rateLimited(int $retryAfter = 60): self
    {
        return new self(
            "LLM rate limit exceeded. Retry after {$retryAfter} seconds.",
            429,
            null,
            ['retry_after' => $retryAfter]
        );
    }

    /**
     * Create exception for timeout.
     */
    public static function timeout(): self
    {
        return new self("LLM request timed out", 408);
    }

    /**
     * Get the context data.
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
