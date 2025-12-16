<?php

namespace Tests\Unit\Chatbot;

use App\DTO\RouterOutput;
use App\Services\Chatbot\ChatRouterService;
use PHPUnit\Framework\TestCase;

class RouterOutputTest extends TestCase
{
    /**
     * Test RouterOutput creation from array.
     */
    public function test_router_output_from_array(): void
    {
        $data = [
            'intent' => 'product_search',
            'filters' => [
                'query' => 'benih tomat',
                'category' => 'sayuran',
            ],
            'sources' => ['products'],
            'confidence' => 0.85,
            'clarification_needed' => null,
        ];

        $output = RouterOutput::fromArray($data);

        $this->assertEquals('product_search', $output->intent);
        $this->assertEquals(['query' => 'benih tomat', 'category' => 'sayuran'], $output->filters);
        $this->assertEquals(['products'], $output->sources);
        $this->assertEquals(0.85, $output->confidence);
        $this->assertNull($output->clarificationNeeded);
    }

    /**
     * Test RouterOutput handles missing fields gracefully.
     */
    public function test_router_output_handles_missing_fields(): void
    {
        $data = [
            'intent' => 'greeting',
        ];

        $output = RouterOutput::fromArray($data);

        $this->assertEquals('greeting', $output->intent);
        $this->assertEquals([], $output->filters);
        $this->assertEquals([], $output->sources);
        $this->assertEquals(0.0, $output->confidence);
        $this->assertNull($output->clarificationNeeded);
    }

    /**
     * Test needsClarification logic.
     */
    public function test_needs_clarification_when_low_confidence(): void
    {
        $output = new RouterOutput(
            intent: 'product_search',
            filters: [],
            sources: ['products'],
            confidence: 0.3,
        );

        $this->assertTrue($output->needsClarification());
    }

    /**
     * Test needsClarification when clarification message is set.
     */
    public function test_needs_clarification_when_message_set(): void
    {
        $output = new RouterOutput(
            intent: 'unknown',
            filters: [],
            sources: [],
            confidence: 0.8,
            clarificationNeeded: 'Bisa tolong perjelas?',
        );

        $this->assertTrue($output->needsClarification());
    }

    /**
     * Test no clarification needed when confidence is high.
     */
    public function test_no_clarification_when_high_confidence(): void
    {
        $output = new RouterOutput(
            intent: 'product_search',
            filters: ['query' => 'tomat'],
            sources: ['products'],
            confidence: 0.85,
        );

        $this->assertFalse($output->needsClarification());
    }

    /**
     * Test isOutOfScope detection.
     */
    public function test_is_out_of_scope(): void
    {
        $output = new RouterOutput(
            intent: 'out_of_scope',
            filters: [],
            sources: [],
            confidence: 0.9,
        );

        $this->assertTrue($output->isOutOfScope());
    }

    /**
     * Test isGeneralChat detection.
     */
    public function test_is_general_chat(): void
    {
        $greetingOutput = new RouterOutput(
            intent: 'greeting',
            filters: [],
            sources: [],
            confidence: 0.95,
        );

        $thanksOutput = new RouterOutput(
            intent: 'thanks',
            filters: [],
            sources: [],
            confidence: 0.95,
        );

        $farewellOutput = new RouterOutput(
            intent: 'farewell',
            filters: [],
            sources: [],
            confidence: 0.95,
        );

        $this->assertTrue($greetingOutput->isGeneralChat());
        $this->assertTrue($thanksOutput->isGeneralChat());
        $this->assertTrue($farewellOutput->isGeneralChat());
    }

    /**
     * Test product search is not general chat.
     */
    public function test_product_search_is_not_general_chat(): void
    {
        $output = new RouterOutput(
            intent: 'product_search',
            filters: ['query' => 'tomat'],
            sources: ['products'],
            confidence: 0.85,
        );

        $this->assertFalse($output->isGeneralChat());
    }

    /**
     * Test JSON serialization.
     */
    public function test_json_serialization(): void
    {
        $output = new RouterOutput(
            intent: 'product_search',
            filters: ['query' => 'benih cabai'],
            sources: ['products', 'articles'],
            confidence: 0.75,
        );

        $json = json_encode($output);
        $decoded = json_decode($json, true);

        $this->assertEquals('product_search', $decoded['intent']);
        $this->assertEquals(['query' => 'benih cabai'], $decoded['filters']);
        $this->assertEquals(['products', 'articles'], $decoded['sources']);
        $this->assertEquals(0.75, $decoded['confidence']);
    }
}
