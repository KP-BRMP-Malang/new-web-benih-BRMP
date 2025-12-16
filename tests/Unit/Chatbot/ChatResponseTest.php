<?php

namespace Tests\Unit\Chatbot;

use App\DTO\ChatResponse;
use App\DTO\RetrievalCandidate;
use PHPUnit\Framework\TestCase;

class ChatResponseTest extends TestCase
{
    /**
     * Test found response creation.
     */
    public function test_found_response(): void
    {
        $data = [
            ['title' => 'Benih Tomat', 'price' => 15000],
        ];

        $response = ChatResponse::found('product', 'Berikut produk yang ditemukan:', $data);

        $this->assertEquals('found', $response->status);
        $this->assertEquals('product', $response->type);
        $this->assertEquals('Berikut produk yang ditemukan:', $response->message);
        $this->assertCount(1, $response->data);
    }

    /**
     * Test not found response.
     */
    public function test_not_found_response(): void
    {
        $response = ChatResponse::notFound('Produk tidak ditemukan');

        $this->assertEquals('not_found', $response->status);
        $this->assertEquals('warning', $response->type);
        $this->assertEquals('Produk tidak ditemukan', $response->message);
        $this->assertEmpty($response->data);
    }

    /**
     * Test need clarification response.
     */
    public function test_need_clarification_response(): void
    {
        $response = ChatResponse::needClarification('Bisa lebih spesifik?');

        $this->assertEquals('need_clarification', $response->status);
        $this->assertEquals('chat', $response->type);
        $this->assertEquals('Bisa lebih spesifik?', $response->message);
    }

    /**
     * Test out of scope response.
     */
    public function test_out_of_scope_response(): void
    {
        $response = ChatResponse::outOfScope('Di luar topik');

        $this->assertEquals('out_of_scope', $response->status);
        $this->assertEquals('warning', $response->type);
    }

    /**
     * Test chat response (for greetings).
     */
    public function test_chat_response(): void
    {
        $response = ChatResponse::chat('Halo! Ada yang bisa dibantu?');

        $this->assertEquals('success', $response->status);
        $this->assertEquals('chat', $response->type);
        $this->assertEquals('Halo! Ada yang bisa dibantu?', $response->message);
    }

    /**
     * Test error response.
     */
    public function test_error_response(): void
    {
        $response = ChatResponse::error('Terjadi kesalahan');

        $this->assertEquals('error', $response->status);
        $this->assertEquals('warning', $response->type);
    }

    /**
     * Test toArray method.
     */
    public function test_to_array(): void
    {
        $response = ChatResponse::found('product', 'Test', [['title' => 'A']]);
        $array = $response->toArray();

        $this->assertArrayHasKey('status', $array);
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayHasKey('message', $array);
        $this->assertArrayHasKey('data', $array);
    }

    /**
     * Test JSON serialization.
     */
    public function test_json_serialization(): void
    {
        $response = ChatResponse::found('article', 'Artikel ditemukan', [
            ['title' => 'Cara Menanam Tomat', 'link' => '/artikel/1'],
        ]);

        $json = json_encode($response);
        $decoded = json_decode($json, true);

        $this->assertEquals('found', $decoded['status']);
        $this->assertEquals('article', $decoded['type']);
        $this->assertCount(1, $decoded['data']);
    }
}
