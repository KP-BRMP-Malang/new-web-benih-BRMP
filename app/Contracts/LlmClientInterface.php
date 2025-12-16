<?php

namespace App\Contracts;

/**
 * Interface for LLM client implementations.
 * Allows easy swapping between different LLM providers (Gemini, OpenAI, etc).
 */
interface LlmClientInterface
{
    /**
     * Send a prompt to the LLM and get a response.
     *
     * @param string $prompt The prompt to send
     * @param array $options Additional options (temperature, maxTokens, etc)
     * @return array{content: string, usage: array}
     * @throws \App\Exceptions\LlmException
     */
    public function generate(string $prompt, array $options = []): array;

    /**
     * Send a structured prompt expecting JSON output.
     *
     * @param string $prompt The prompt to send
     * @param array $options Additional options
     * @return array Parsed JSON response
     * @throws \App\Exceptions\LlmException
     */
    public function generateJson(string $prompt, array $options = []): array;

    /**
     * Send a chat-style conversation to the LLM.
     *
     * @param array $messages Array of messages with role and content
     * @param array $options Additional options
     * @return array{content: string, usage: array}
     * @throws \App\Exceptions\LlmException
     */
    public function chat(array $messages, array $options = []): array;

    /**
     * Get the model name being used.
     */
    public function getModelName(): string;
}
