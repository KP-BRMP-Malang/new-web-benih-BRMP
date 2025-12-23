<?php

namespace App\Services\LLM;

use App\Contracts\LlmClientInterface;
use App\Exceptions\LlmException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google Gemini API client implementation.
 */
class GeminiClient implements LlmClientInterface
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-1.5-flash');
        $this->baseUrl = config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1');
        $this->timeout = config('services.gemini.timeout', 30);

        if (empty($this->apiKey)) {
            throw new \RuntimeException('Gemini API key is not configured');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generate(string $prompt, array $options = []): array
    {
        $payload = $this->buildPayload($prompt, $options);

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($this->getEndpoint(), $payload);

            if ($response->failed()) {
                $this->handleErrorResponse($response);
            }

            $data = $response->json();
            $content = $this->extractContent($data);
            $usage = $this->extractUsage($data);

            Log::debug('Gemini API response', [
                'model' => $this->model,
                'prompt_length' => strlen($prompt),
                'usage' => $usage,
            ]);

            return [
                'content' => $content,
                'usage' => $usage,
            ];
        } catch (LlmException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Gemini API error', [
                'error' => $e->getMessage(),
                'prompt_length' => strlen($prompt),
            ]);
            throw LlmException::apiError($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generateJson(string $prompt, array $options = []): array
    {
        // Add JSON instruction to prompt
        $jsonPrompt = $prompt . "\n\nIMPORTANT: Respond ONLY with valid JSON. No markdown, no code blocks, no explanation.";

        // Set generation config for JSON output
        $options['response_mime_type'] = 'application/json';

        $result = $this->generate($jsonPrompt, $options);
        $content = $result['content'];

        // Clean up common issues with JSON responses
        $content = $this->cleanJsonResponse($content);

        try {
            $parsed = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            return $parsed;
        } catch (\JsonException $e) {
            Log::warning('Gemini returned invalid JSON', [
                'content' => $content,
                'error' => $e->getMessage(),
            ]);
            throw LlmException::invalidJson($content);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function chat(array $messages, array $options = []): array
    {
        $contents = [];
        foreach ($messages as $message) {
            $role = $message['role'] === 'assistant' ? 'model' : 'user';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $message['content']]],
            ];
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => $this->buildGenerationConfig($options),
        ];

        if (isset($options['system_instruction'])) {
            $payload['systemInstruction'] = [
                'parts' => [['text' => $options['system_instruction']]],
            ];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($this->getEndpoint(), $payload);

            if ($response->failed()) {
                $this->handleErrorResponse($response);
            }

            $data = $response->json();
            $content = $this->extractContent($data);
            $usage = $this->extractUsage($data);

            return [
                'content' => $content,
                'usage' => $usage,
            ];
        } catch (LlmException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Gemini chat API error', ['error' => $e->getMessage()]);
            throw LlmException::apiError($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getModelName(): string
    {
        return $this->model;
    }

    /**
     * Build the request payload.
     */
    private function buildPayload(string $prompt, array $options): array
    {
        $payload = [
            'contents' => [
                [
                    'parts' => [['text' => $prompt]],
                ],
            ],
            'generationConfig' => $this->buildGenerationConfig($options),
        ];

        if (isset($options['system_instruction'])) {
            $payload['systemInstruction'] = [
                'parts' => [['text' => $options['system_instruction']]],
            ];
        }

        return $payload;
    }

    /**
     * Build generation config from options.
     */
    private function buildGenerationConfig(array $options): array
    {
        $config = [
            'temperature' => $options['temperature'] ?? 0.7,
            'topK' => $options['top_k'] ?? 40,
            'topP' => $options['top_p'] ?? 0.95,
            'maxOutputTokens' => $options['max_tokens'] ?? 1024,
        ];

        // responseMimeType is not supported in v1 API, only in v1beta
        // Removed to maintain compatibility with v1 endpoint

        return $config;
    }

    /**
     * Get the API endpoint URL.
     */
    private function getEndpoint(): string
    {
        return "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";
    }

    /**
     * Extract content from API response.
     */
    private function extractContent(array $data): string
    {
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return $data['candidates'][0]['content']['parts'][0]['text'];
        }

        if (isset($data['error'])) {
            throw LlmException::apiError($data['error']['message'] ?? 'Unknown error');
        }

        throw LlmException::apiError('Unable to extract content from response');
    }

    /**
     * Extract usage/token info from response.
     */
    private function extractUsage(array $data): array
    {
        return [
            'prompt_tokens' => $data['usageMetadata']['promptTokenCount'] ?? 0,
            'completion_tokens' => $data['usageMetadata']['candidatesTokenCount'] ?? 0,
            'total_tokens' => $data['usageMetadata']['totalTokenCount'] ?? 0,
        ];
    }

    /**
     * Handle error response from API.
     */
    private function handleErrorResponse($response): void
    {
        $status = $response->status();
        $body = $response->json();

        if ($status === 429) {
            throw LlmException::rateLimited();
        }

        if ($status === 408) {
            throw LlmException::timeout();
        }

        $message = $body['error']['message'] ?? "HTTP {$status} error";
        throw LlmException::apiError($message, $body);
    }

    /**
     * Clean up JSON response from common formatting issues.
     */
    private function cleanJsonResponse(string $content): string
    {
        // Remove markdown code blocks
        $content = preg_replace('/^```json?\s*/i', '', $content);
        $content = preg_replace('/\s*```$/i', '', $content);

        // Trim whitespace
        $content = trim($content);

        return $content;
    }
}
