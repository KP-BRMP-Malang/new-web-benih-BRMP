<?php

namespace App\Services\Chatbot;

use App\DTO\ChatResponse;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Support\Facades\Log;

/**
 * Orchestrator service that coordinates the chatbot flow:
 * Router → Retrieval → Composer
 */
class ChatbotOrchestratorService
{
    private ChatRouterService $router;
    private RetrievalService $retrieval;
    private ChatComposerService $composer;

    public function __construct(
        ChatRouterService $router,
        RetrievalService $retrieval,
        ChatComposerService $composer
    ) {
        $this->router = $router;
        $this->retrieval = $retrieval;
        $this->composer = $composer;
    }

    /**
     * Process a chat message through the full pipeline.
     *
     * @param string $sessionToken Session identifier
     * @param string $message User message
     * @param int|null $userId Optional user ID
     * @return ChatResponse
     */
    public function process(string $sessionToken, string $message, ?int $userId = null): ChatResponse
    {
        $startTime = microtime(true);

        try {
            // 1. Get or create session
            $session = ChatSession::findOrCreateByToken($sessionToken, $userId);
            $session->updateLastActivity();

            // 2. Save user message
            $userMessage = ChatMessage::createUserMessage($session->id, $message);

            // 3. Get conversation history for context
            $history = $this->getConversationHistory($session);

            // 4. STAGE 1: Route the message
            Log::info('Chatbot: Starting routing', ['session' => $sessionToken]);
            $routerOutput = $this->router->route($message, $history);

            Log::info('Chatbot: Router output', [
                'intent' => $routerOutput->intent,
                'sources' => $routerOutput->sources,
                'confidence' => $routerOutput->confidence,
            ]);

            // 5. STAGE 2: Retrieve candidates (if needed)
            $candidates = [];
            if (!empty($routerOutput->sources) && !$routerOutput->needsClarification()) {
                Log::info('Chatbot: Starting retrieval', ['sources' => $routerOutput->sources]);
                $candidates = $this->retrieval->retrieve($routerOutput);

                Log::info('Chatbot: Retrieved candidates', [
                    'count' => count($candidates),
                ]);
            }

            // 6. STAGE 3: Compose response
            Log::info('Chatbot: Starting composition');
            $response = $this->composer->compose($message, $routerOutput, $candidates, $history);

            // 7. Save assistant response
            $metadata = [
                'intent' => $routerOutput->intent,
                'confidence' => $routerOutput->confidence,
                'sources' => $routerOutput->sources,
                'candidates_count' => count($candidates),
                'response_status' => $response->status,
            ];

            ChatMessage::createAssistantMessage(
                $session->id,
                $response->message,
                $metadata
            );

            // 8. Update session context
            $session->addContext('last_intent', $routerOutput->intent);
            $session->addContext('last_sources', $routerOutput->sources);

            $duration = round((microtime(true) - $startTime) * 1000);
            Log::info('Chatbot: Completed', [
                'session' => $sessionToken,
                'duration_ms' => $duration,
                'status' => $response->status,
            ]);

            return $response;

        } catch (\Exception $e) {
            Log::error('Chatbot: Error processing message', [
                'session' => $sessionToken,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return ChatResponse::error(
                'Maaf, terjadi kesalahan dalam memproses permintaan Anda. Silakan coba lagi.'
            );
        }
    }

    /**
     * Get conversation history for context.
     *
     * @param ChatSession $session
     * @param int $limit Maximum messages to include
     * @return array
     */
    private function getConversationHistory(ChatSession $session, int $limit = 10): array
    {
        $messages = $session->messages()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();

        return $messages->map(fn($m) => [
            'role' => $m->role,
            'content' => $m->content,
        ])->all();
    }

    /**
     * Get chat history for a session (for displaying in UI).
     *
     * @param string $sessionToken
     * @return array
     */
    public function getHistory(string $sessionToken): array
    {
        $session = ChatSession::where('session_token', $sessionToken)->first();

        if (!$session) {
            return [];
        }

        return $session->messages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'role' => $m->role,
                'content' => $m->content,
                'created_at' => $m->created_at->toISOString(),
            ])
            ->all();
    }

    /**
     * Clear chat history for a session.
     *
     * @param string $sessionToken
     * @return bool
     */
    public function clearHistory(string $sessionToken): bool
    {
        $session = ChatSession::where('session_token', $sessionToken)->first();

        if (!$session) {
            return false;
        }

        $session->messages()->delete();
        $session->context = null;
        $session->save();

        return true;
    }
}
