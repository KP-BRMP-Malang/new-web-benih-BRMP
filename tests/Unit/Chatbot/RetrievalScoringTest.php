<?php

namespace Tests\Unit\Chatbot;

use App\Services\Chatbot\RetrievalService;
use Tests\TestCase;

class RetrievalScoringTest extends TestCase
{
    private RetrievalService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RetrievalService();
    }

    /**
     * Test tokenization removes stopwords.
     */
    public function test_tokenize_removes_stopwords(): void
    {
        $text = "saya ingin cari benih tomat untuk dataran tinggi";
        $tokens = $this->service->tokenize($text);

        $this->assertContains('benih', $tokens);
        $this->assertContains('tomat', $tokens);
        $this->assertContains('dataran', $tokens);
        $this->assertContains('tinggi', $tokens);
        
        // Stopwords should be removed
        $this->assertNotContains('saya', $tokens);
        $this->assertNotContains('ingin', $tokens);
        $this->assertNotContains('cari', $tokens);
        $this->assertNotContains('untuk', $tokens);
    }

    /**
     * Test tokenization lowercases text.
     */
    public function test_tokenize_lowercases(): void
    {
        $text = "BENIH Tomat CHERRY";
        $tokens = $this->service->tokenize($text);

        $this->assertContains('benih', $tokens);
        $this->assertContains('tomat', $tokens);
        $this->assertContains('cherry', $tokens);
        
        // No uppercase
        $this->assertNotContains('BENIH', $tokens);
        $this->assertNotContains('Tomat', $tokens);
    }

    /**
     * Test tokenization removes punctuation.
     */
    public function test_tokenize_removes_punctuation(): void
    {
        $text = "benih tomat, cabai! dan terong?";
        $tokens = $this->service->tokenize($text);

        $this->assertContains('benih', $tokens);
        $this->assertContains('tomat', $tokens);
        $this->assertContains('cabai', $tokens);
        $this->assertContains('terong', $tokens);
    }

    /**
     * Test tokenization keeps alphanumeric words.
     */
    public function test_tokenize_keeps_alphanumeric(): void
    {
        $text = "varietas f1 hybrid 10kg";
        $tokens = $this->service->tokenize($text);

        $this->assertContains('varietas', $tokens);
        $this->assertContains('f1', $tokens);
        $this->assertContains('hybrid', $tokens);
        $this->assertContains('10kg', $tokens);
    }

    /**
     * Test tokenization removes pure numbers.
     */
    public function test_tokenize_removes_pure_numbers(): void
    {
        $text = "harga 50000 rupiah";
        $tokens = $this->service->tokenize($text);

        $this->assertContains('harga', $tokens);
        $this->assertContains('rupiah', $tokens);
        $this->assertNotContains('50000', $tokens);
    }

    /**
     * Test tokenization with stopwords kept.
     */
    public function test_tokenize_can_keep_stopwords(): void
    {
        $text = "cara menanam tomat yang baik";
        $tokens = $this->service->tokenize($text, false); // Keep stopwords

        $this->assertContains('cara', $tokens);
        $this->assertContains('menanam', $tokens);
        $this->assertContains('tomat', $tokens);
        $this->assertContains('yang', $tokens); // Stopword kept
        $this->assertContains('baik', $tokens);
    }

    /**
     * Test strong match threshold.
     */
    public function test_is_strong_match(): void
    {
        $this->assertTrue(RetrievalService::isStrongMatch(0.65));
        $this->assertTrue(RetrievalService::isStrongMatch(0.8));
        $this->assertTrue(RetrievalService::isStrongMatch(1.0));
        
        $this->assertFalse(RetrievalService::isStrongMatch(0.64));
        $this->assertFalse(RetrievalService::isStrongMatch(0.45));
    }

    /**
     * Test medium match threshold.
     */
    public function test_is_medium_match(): void
    {
        $this->assertTrue(RetrievalService::isMediumMatch(0.45));
        $this->assertTrue(RetrievalService::isMediumMatch(0.55));
        $this->assertTrue(RetrievalService::isMediumMatch(0.64));
        
        $this->assertFalse(RetrievalService::isMediumMatch(0.65)); // Strong
        $this->assertFalse(RetrievalService::isMediumMatch(0.44)); // Weak
    }

    /**
     * Test weak match threshold.
     */
    public function test_is_weak_match(): void
    {
        $this->assertTrue(RetrievalService::isWeakMatch(0.0));
        $this->assertTrue(RetrievalService::isWeakMatch(0.3));
        $this->assertTrue(RetrievalService::isWeakMatch(0.44));
        
        $this->assertFalse(RetrievalService::isWeakMatch(0.45));
        $this->assertFalse(RetrievalService::isWeakMatch(0.65));
    }

    /**
     * Test threshold constants are properly defined.
     */
    public function test_threshold_constants(): void
    {
        $this->assertEquals(0.65, RetrievalService::THRESHOLD_STRONG);
        $this->assertEquals(0.45, RetrievalService::THRESHOLD_MEDIUM);
    }

    /**
     * Test initial retrieval result state.
     */
    public function test_initial_retrieval_result(): void
    {
        $result = $this->service->getRetrievalResult();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('candidates', $result);
        $this->assertArrayHasKey('top_score', $result);
        $this->assertArrayHasKey('gate_status', $result);
        $this->assertArrayHasKey('clarifying_question', $result);
    }

    /**
     * Test gate status getter.
     */
    public function test_get_gate_status(): void
    {
        $status = $this->service->getGateStatus();
        
        $this->assertIsString($status);
        $this->assertContains($status, ['found', 'need_clarification', 'not_found', 'out_of_scope']);
    }

    /**
     * Test empty query tokenization.
     */
    public function test_tokenize_empty_query(): void
    {
        $tokens = $this->service->tokenize('');
        $this->assertEmpty($tokens);

        $tokens = $this->service->tokenize('   ');
        $this->assertEmpty($tokens);
    }

    /**
     * Test tokenization with short words.
     */
    public function test_tokenize_removes_short_words(): void
    {
        $text = "a b c de tomat";
        $tokens = $this->service->tokenize($text);

        // Single char words removed
        $this->assertNotContains('a', $tokens);
        $this->assertNotContains('b', $tokens);
        $this->assertNotContains('c', $tokens);
        
        // 2+ char words kept
        $this->assertContains('de', $tokens);
        $this->assertContains('tomat', $tokens);
    }
}
