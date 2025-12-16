<?php

namespace Tests\Feature\Chatbot;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test chat endpoint validation - missing session_id.
     */
    public function test_chat_requires_session_id(): void
    {
        $response = $this->postJson('/api/chat', [
            'message' => 'Hello',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['session_id']);
    }

    /**
     * Test chat endpoint validation - missing message.
     */
    public function test_chat_requires_message(): void
    {
        $response = $this->postJson('/api/chat', [
            'session_id' => 'test-session',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    /**
     * Test chat endpoint validation - message too long.
     */
    public function test_chat_message_max_length(): void
    {
        $response = $this->postJson('/api/chat', [
            'session_id' => 'test-session',
            'message' => str_repeat('a', 2001),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    /**
     * Test history endpoint requires session_id.
     */
    public function test_history_requires_session_id(): void
    {
        $response = $this->getJson('/api/chat/history');

        $response->assertStatus(400)
            ->assertJson(['error' => 'session_id is required']);
    }

    /**
     * Test clear history requires session_id.
     */
    public function test_clear_history_requires_session_id(): void
    {
        $response = $this->deleteJson('/api/chat/history');

        $response->assertStatus(400)
            ->assertJson(['error' => 'session_id is required']);
    }

    /**
     * Test health endpoint.
     */
    public function test_health_endpoint(): void
    {
        $response = $this->getJson('/api/chat/health');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'service', 'timestamp'])
            ->assertJson(['status' => 'ok', 'service' => 'chatbot']);
    }
}
