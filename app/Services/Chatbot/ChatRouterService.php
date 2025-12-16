<?php

namespace App\Services\Chatbot;

use App\Contracts\LlmClientInterface;
use App\DTO\RouterOutput;
use Illuminate\Support\Facades\Log;

/**
 * Service for routing chat messages to appropriate data sources.
 * Uses LLM to classify intent and extract filters.
 */
class ChatRouterService
{
    private LlmClientInterface $llmClient;
    private PromptRepository $promptRepository;

    public function __construct(LlmClientInterface $llmClient, PromptRepository $promptRepository)
    {
        $this->llmClient = $llmClient;
        $this->promptRepository = $promptRepository;
    }

    /**
     * Route a user message to determine intent and data sources.
     *
     * @param string $userMessage The user's message
     * @param array $conversationHistory Recent conversation for context
     * @return RouterOutput
     */
    public function route(string $userMessage, array $conversationHistory = []): RouterOutput
    {
        $prompt = $this->buildRouterPrompt($userMessage, $conversationHistory);
        
        // Retry up to 2 times for transient errors
        $maxRetries = 2;
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $result = $this->llmClient->generateJson($prompt, [
                    'temperature' => 0.2, // Lower temperature for more consistent routing
                    'max_tokens' => 1024, // Increased to prevent truncated responses
                ]);

                Log::debug('Router LLM output', ['result' => $result, 'message' => $userMessage]);

                return $this->parseRouterResult($result);
            } catch (\Exception $e) {
                $lastException = $e;
                $errorMessage = $e->getMessage();
                
                // Don't retry rate limit errors
                if (str_contains($errorMessage, 'rate limit') || str_contains($errorMessage, '429')) {
                    Log::error('Router LLM rate limit', ['error' => $errorMessage, 'message' => $userMessage]);
                    return new RouterOutput(
                        intent: 'error',
                        filters: [],
                        sources: [],
                        confidence: 0.0,
                        clarificationNeeded: 'Maaf, sistem sedang sibuk karena banyak permintaan. Silakan tunggu 1 menit dan coba lagi.'
                    );
                }
                
                // Log and retry for invalid JSON (truncated response)
                if (str_contains($errorMessage, 'invalid JSON') && $attempt < $maxRetries) {
                    Log::warning('Router LLM invalid JSON, retrying', [
                        'attempt' => $attempt,
                        'error' => $errorMessage,
                        'message' => $userMessage,
                    ]);
                    usleep(500000); // Wait 500ms before retry
                    continue;
                }
                
                Log::error('Router LLM error', [
                    'error' => $errorMessage,
                    'message' => $userMessage,
                    'attempt' => $attempt,
                ]);
            }
        }

        // All retries failed
        return new RouterOutput(
            intent: 'unknown',
            filters: [],
            sources: [],
            confidence: 0.0,
            clarificationNeeded: 'Maaf, saya tidak dapat memproses permintaan Anda. Bisa tolong ulangi dengan lebih spesifik?'
        );
    }

    /**
     * Build the router prompt using PromptRepository.
     */
    private function buildRouterPrompt(string $userMessage, array $conversationHistory): string
    {
        $basePrompt = $this->promptRepository->getRouterPrompt();
        
        $contextSection = '';
        if (!empty($conversationHistory)) {
            $contextSection = "\n═══════════════════════════════════════════════════════════════\nHISTORI PERCAKAPAN (Untuk konteks):\n═══════════════════════════════════════════════════════════════\n";
            foreach (array_slice($conversationHistory, -5) as $msg) {
                $role = $msg['role'] === 'user' ? 'User' : 'Assistant';
                $contextSection .= "{$role}: {$msg['content']}\n";
            }
        }

        return $basePrompt . $contextSection . "\nUser: " . $userMessage;
    }

    /**
     * Parse router result and convert to RouterOutput.
     * Handles different field names from LLM response.
     */
    private function parseRouterResult(array $result): RouterOutput
    {
        // Map needs_sources to sources (handle both field names)
        $sources = $result['needs_sources'] ?? $result['sources'] ?? [];
        
        // Map search_query to query in filters
        $filters = $result['filters'] ?? [];
        if (!empty($result['search_query']) && empty($filters['query'])) {
            $filters['query'] = $result['search_query'];
        }
        
        // Handle clarification
        $clarification = $result['clarifying_question'] ?? $result['clarification_needed'] ?? null;
        $needsClarification = $result['needs_clarification'] ?? !empty($clarification);
        
        Log::debug('Parsed router result', [
            'intent' => $result['intent'] ?? 'unknown',
            'sources' => $sources,
            'filters' => $filters,
            'clarification' => $clarification,
        ]);
        
        return new RouterOutput(
            intent: $result['intent'] ?? 'unknown',
            filters: $filters,
            sources: $sources,
            confidence: (float) ($result['confidence'] ?? 0.5),
            clarificationNeeded: $needsClarification ? ($clarification ?? 'Bisa diperjelas pertanyaan Anda?') : null
        );
    }

    /**
     * Parse router output with validation and fallback.
     * @deprecated Use parseRouterResult instead
     */
    public function parseOutput(array $rawOutput): RouterOutput
    {
        return $this->parseRouterResult($rawOutput);
    }
}
