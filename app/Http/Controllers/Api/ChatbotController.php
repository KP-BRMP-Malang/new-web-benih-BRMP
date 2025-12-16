<?php

namespace App\Http\Controllers\Api;

use App\DTO\ChatResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChatRequest;
use App\Services\Chatbot\ChatbotOrchestratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    private ChatbotOrchestratorService $orchestrator;

    public function __construct(ChatbotOrchestratorService $orchestrator)
    {
        $this->orchestrator = $orchestrator;
    }

    /**
     * Handle incoming chat message.
     *
     * POST /api/chat
     * Body: { session_id: string, message: string }
     *
     * @param ChatRequest $request
     * @return JsonResponse
     */
    public function chat(ChatRequest $request): JsonResponse
    {
        $sessionId = $request->getSessionId();
        $message = $request->getMessage();
        $userId = $request->user()?->id;

        $response = $this->orchestrator->process($sessionId, $message, $userId);

        return response()->json($response->toArray());
    }

    /**
     * Get chat history for a session.
     *
     * GET /api/chat/history?session_id=xxx
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function history(Request $request): JsonResponse
    {
        $sessionId = $request->query('session_id');

        if (empty($sessionId)) {
            return response()->json([
                'error' => 'session_id is required',
            ], 400);
        }

        $history = $this->orchestrator->getHistory($sessionId);

        return response()->json([
            'session_id' => $sessionId,
            'messages' => $history,
        ]);
    }

    /**
     * Clear chat history for a session.
     *
     * DELETE /api/chat/history?session_id=xxx
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function clearHistory(Request $request): JsonResponse
    {
        $sessionId = $request->query('session_id');

        if (empty($sessionId)) {
            return response()->json([
                'error' => 'session_id is required',
            ], 400);
        }

        $cleared = $this->orchestrator->clearHistory($sessionId);

        if (!$cleared) {
            return response()->json([
                'error' => 'Session not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Chat history cleared',
        ]);
    }

    /**
     * Health check endpoint.
     *
     * GET /api/chat/health
     *
     * @return JsonResponse
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'chatbot',
            'timestamp' => now()->toISOString(),
        ]);
    }
}
