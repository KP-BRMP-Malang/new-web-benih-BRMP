<?php

namespace Tests\Feature\Chatbot;

use App\Contracts\LlmClientInterface;
use App\DTO\RouterOutput;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Services\Chatbot\ChatbotOrchestratorService;
use App\Services\Chatbot\ChatComposerService;
use App\Services\Chatbot\ChatRouterService;
use App\Services\Chatbot\RetrievalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ChatbotOrchestratorTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test greeting flow without database retrieval.
     */
    public function test_greeting_flow(): void
    {
        // Mock LLM client
        $mockLlm = Mockery::mock(LlmClientInterface::class);
        $mockLlm->shouldReceive('generateJson')
            ->once()
            ->andReturn([
                'intent' => 'greeting',
                'filters' => [],
                'sources' => [],
                'confidence' => 0.95,
                'clarification_needed' => null,
            ]);

        // Create services with mock
        $router = new ChatRouterService($mockLlm);
        $retrieval = new RetrievalService();
        $composer = new ChatComposerService($mockLlm);

        $orchestrator = new ChatbotOrchestratorService($router, $retrieval, $composer);

        // Process greeting
        $response = $orchestrator->process('test-session-123', 'Halo!');

        // Assert response
        $this->assertEquals('success', $response->status);
        $this->assertEquals('chat', $response->type);
        $this->assertNotEmpty($response->message);

        // Assert session and messages were created
        $this->assertDatabaseHas('chat_sessions', [
            'session_token' => 'test-session-123',
        ]);

        $session = ChatSession::where('session_token', 'test-session-123')->first();
        $this->assertDatabaseHas('chat_messages', [
            'chat_session_id' => $session->id,
            'role' => 'user',
            'content' => 'Halo!',
        ]);
        $this->assertDatabaseHas('chat_messages', [
            'chat_session_id' => $session->id,
            'role' => 'assistant',
        ]);
    }

    /**
     * Test out of scope detection.
     */
    public function test_out_of_scope_flow(): void
    {
        $mockLlm = Mockery::mock(LlmClientInterface::class);
        $mockLlm->shouldReceive('generateJson')
            ->once()
            ->andReturn([
                'intent' => 'out_of_scope',
                'filters' => [],
                'sources' => [],
                'confidence' => 0.9,
                'clarification_needed' => null,
            ]);

        $router = new ChatRouterService($mockLlm);
        $retrieval = new RetrievalService();
        $composer = new ChatComposerService($mockLlm);

        $orchestrator = new ChatbotOrchestratorService($router, $retrieval, $composer);

        $response = $orchestrator->process('test-session-456', 'Siapa presiden Indonesia?');

        $this->assertEquals('out_of_scope', $response->status);
        $this->assertEquals('warning', $response->type);
    }

    /**
     * Test clarification needed when confidence is low.
     */
    public function test_clarification_needed_flow(): void
    {
        $mockLlm = Mockery::mock(LlmClientInterface::class);
        $mockLlm->shouldReceive('generateJson')
            ->once()
            ->andReturn([
                'intent' => 'unknown',
                'filters' => [],
                'sources' => [],
                'confidence' => 0.2,
                'clarification_needed' => 'Maaf, bisa diperjelas maksudnya?',
            ]);

        $router = new ChatRouterService($mockLlm);
        $retrieval = new RetrievalService();
        $composer = new ChatComposerService($mockLlm);

        $orchestrator = new ChatbotOrchestratorService($router, $retrieval, $composer);

        $response = $orchestrator->process('test-session-789', 'xyz');

        $this->assertEquals('need_clarification', $response->status);
        $this->assertEquals('chat', $response->type);
    }

    /**
     * Test get history functionality.
     */
    public function test_get_history(): void
    {
        // Create a session with messages
        $session = ChatSession::create([
            'session_token' => 'history-test-session',
            'status' => 'active',
        ]);

        ChatMessage::createUserMessage($session->id, 'Halo');
        ChatMessage::createAssistantMessage($session->id, 'Hai! Ada yang bisa dibantu?');
        ChatMessage::createUserMessage($session->id, 'Cari benih tomat');

        $mockLlm = Mockery::mock(LlmClientInterface::class);
        $router = new ChatRouterService($mockLlm);
        $retrieval = new RetrievalService();
        $composer = new ChatComposerService($mockLlm);

        $orchestrator = new ChatbotOrchestratorService($router, $retrieval, $composer);

        $history = $orchestrator->getHistory('history-test-session');

        $this->assertCount(3, $history);
        $this->assertEquals('user', $history[0]['role']);
        $this->assertEquals('Halo', $history[0]['content']);
        $this->assertEquals('assistant', $history[1]['role']);
        $this->assertEquals('user', $history[2]['role']);
    }

    /**
     * Test clear history functionality.
     */
    public function test_clear_history(): void
    {
        $session = ChatSession::create([
            'session_token' => 'clear-test-session',
            'status' => 'active',
        ]);

        ChatMessage::createUserMessage($session->id, 'Test message');
        ChatMessage::createAssistantMessage($session->id, 'Test response');

        $mockLlm = Mockery::mock(LlmClientInterface::class);
        $router = new ChatRouterService($mockLlm);
        $retrieval = new RetrievalService();
        $composer = new ChatComposerService($mockLlm);

        $orchestrator = new ChatbotOrchestratorService($router, $retrieval, $composer);

        $result = $orchestrator->clearHistory('clear-test-session');

        $this->assertTrue($result);
        $this->assertDatabaseMissing('chat_messages', [
            'chat_session_id' => $session->id,
        ]);
    }

    /**
     * Test session not found for clear history.
     */
    public function test_clear_history_session_not_found(): void
    {
        $mockLlm = Mockery::mock(LlmClientInterface::class);
        $router = new ChatRouterService($mockLlm);
        $retrieval = new RetrievalService();
        $composer = new ChatComposerService($mockLlm);

        $orchestrator = new ChatbotOrchestratorService($router, $retrieval, $composer);

        $result = $orchestrator->clearHistory('nonexistent-session');

        $this->assertFalse($result);
    }
}
