<?php

namespace Tests\Unit\Chatbot;

use App\DTO\RetrievalCandidate;
use Tests\TestCase;

class RetrievalCandidateTest extends TestCase
{
    /**
     * Test product candidate creation.
     */
    public function test_from_product(): void
    {
        $product = (object) [
            'product_id' => 1,
            'product_name' => 'Benih Tomat Cherry',
            'description' => 'Benih tomat cherry berkualitas tinggi',
            'price_per_unit' => 25000,
            'stock' => 100,
            'unit' => 'pack',
            'minimum_purchase' => 1,
            'plant_type_id' => 2,
        ];

        $candidate = RetrievalCandidate::fromProduct($product, 0.85);

        $this->assertEquals('product', $candidate->type);
        $this->assertEquals(1, $candidate->id);
        $this->assertEquals('Benih Tomat Cherry', $candidate->title);
        $this->assertEquals(25000, $candidate->price);
        $this->assertEquals(100, $candidate->stock);
        $this->assertEquals('pack', $candidate->unit);
        $this->assertEquals(0.85, $candidate->score);
        $this->assertStringContainsString('/produk/1', $candidate->link);
    }

    /**
     * Test article candidate creation.
     */
    public function test_from_article(): void
    {
        $article = (object) [
            'article_id' => 5,
            'headline' => 'Cara Menanam Tomat di Polybag',
            'body' => 'Menanam tomat di polybag merupakan solusi praktis untuk berkebun di lahan terbatas. Berikut langkah-langkahnya...',
            'image_url' => '/images/tomat.jpg',
            'created_at' => '2024-01-15 10:00:00',
        ];

        $candidate = RetrievalCandidate::fromArticle($article, 0.75);

        $this->assertEquals('article', $candidate->type);
        $this->assertEquals(5, $candidate->id);
        $this->assertEquals('Cara Menanam Tomat di Polybag', $candidate->title);
        $this->assertNull($candidate->price);
        $this->assertNull($candidate->stock);
        $this->assertEquals(0.75, $candidate->score);
        $this->assertStringContainsString('/artikel/5', $candidate->link);
    }

    /**
     * Test FAQ candidate creation - link should be null.
     */
    public function test_from_faq(): void
    {
        $faq = (object) [
            'id' => 3,
            'keywords' => 'pengiriman,ongkir,kirim',
            'question' => 'Bagaimana cara pengiriman?',
            'answer' => 'Kami mengirim melalui ekspedisi JNE, J&T, dan SiCepat.',
        ];

        $candidate = RetrievalCandidate::fromFaq($faq, 0.9);

        $this->assertEquals('faq', $candidate->type);
        $this->assertEquals(3, $candidate->id);
        $this->assertEquals('Bagaimana cara pengiriman?', $candidate->title);
        $this->assertEquals('Kami mengirim melalui ekspedisi JNE, J&T, dan SiCepat.', $candidate->summary);
        $this->assertEquals(0.9, $candidate->score);
        $this->assertNull($candidate->link); // FAQ tidak punya link
    }

    /**
     * Test JSON serialization for product.
     * Expected fields: id, title, price, stock, unit, summary, link
     */
    public function test_product_json_serialization(): void
    {
        $candidate = new RetrievalCandidate(
            type: 'product',
            id: 1,
            title: 'Benih Cabai',
            summary: 'Benih cabai rawit',
            price: 15000,
            stock: 50,
            unit: 'sachet',
            link: '/produk/1',
            score: 0.8,
        );

        $json = $candidate->jsonSerialize();

        // Product harus punya fields: type, id, title, price, stock, unit, summary, link
        $this->assertArrayHasKey('type', $json);
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('title', $json);
        $this->assertArrayHasKey('price', $json);
        $this->assertArrayHasKey('stock', $json);
        $this->assertArrayHasKey('unit', $json);
        $this->assertArrayHasKey('summary', $json);
        $this->assertArrayHasKey('link', $json);
        
        $this->assertEquals('product', $json['type']);
        $this->assertEquals(1, $json['id']);
        $this->assertEquals('Benih Cabai', $json['title']);
        $this->assertEquals(15000, $json['price']);
        $this->assertEquals(50, $json['stock']);
        $this->assertEquals('sachet', $json['unit']);
        $this->assertEquals('/produk/1', $json['link']);
    }

    /**
     * Test JSON serialization for article.
     * Expected fields: id, title, summary, link
     */
    public function test_article_json_serialization(): void
    {
        $candidate = new RetrievalCandidate(
            type: 'article',
            id: 2,
            title: 'Tips Berkebun',
            summary: 'Tips berkebun untuk pemula',
            link: '/artikel/2',
            score: 0.7,
        );

        $json = $candidate->jsonSerialize();

        // Article harus punya fields: type, id, title, summary, link
        $this->assertArrayHasKey('type', $json);
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('title', $json);
        $this->assertArrayHasKey('summary', $json);
        $this->assertArrayHasKey('link', $json);
        
        // Article tidak boleh punya price, stock, unit
        $this->assertArrayNotHasKey('price', $json);
        $this->assertArrayNotHasKey('stock', $json);
        $this->assertArrayNotHasKey('unit', $json);
        
        $this->assertEquals('article', $json['type']);
        $this->assertEquals(2, $json['id']);
        $this->assertEquals('Tips Berkebun', $json['title']);
        $this->assertEquals('/artikel/2', $json['link']);
    }

    /**
     * Test JSON serialization for FAQ.
     * Expected fields: id, question, answer, link (null)
     */
    public function test_faq_json_serialization(): void
    {
        $candidate = new RetrievalCandidate(
            type: 'faq',
            id: 3,
            title: 'Bagaimana cara pengiriman?',
            summary: 'Kami mengirim via JNE dan J&T',
            link: null, // FAQ tidak punya link
            score: 0.9,
        );

        $json = $candidate->jsonSerialize();

        // FAQ harus punya fields: type, id, question, answer, link (null)
        $this->assertArrayHasKey('type', $json);
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('question', $json);
        $this->assertArrayHasKey('answer', $json);
        $this->assertArrayHasKey('link', $json);
        
        // FAQ tidak boleh punya title/summary langsung (pakai question/answer)
        $this->assertArrayNotHasKey('title', $json);
        $this->assertArrayNotHasKey('summary', $json);
        
        $this->assertEquals('faq', $json['type']);
        $this->assertEquals(3, $json['id']);
        $this->assertEquals('Bagaimana cara pengiriman?', $json['question']);
        $this->assertEquals('Kami mengirim via JNE dan J&T', $json['answer']);
        $this->assertNull($json['link']); // FAQ link selalu null
    }

    /**
     * Test truncation of long summary.
     */
    public function test_summary_truncation(): void
    {
        $longBody = str_repeat('Lorem ipsum dolor sit amet. ', 50);
        
        $article = (object) [
            'article_id' => 10,
            'headline' => 'Test Article',
            'body' => $longBody,
            'image_url' => null,
            'created_at' => now()->toString(),
        ];

        $candidate = RetrievalCandidate::fromArticle($article);

        // Summary should be truncated to ~200 chars + '...'
        $this->assertLessThanOrEqual(203, strlen($candidate->summary));
        $this->assertStringEndsWith('...', $candidate->summary);
    }
}
