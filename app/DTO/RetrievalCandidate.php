<?php

namespace App\DTO;

use JsonSerializable;

/**
 * Data Transfer Object for retrieved candidates from database.
 * 
 * Output format per type:
 * - Products: id, title, price, stock, unit, summary, link
 * - Articles: id, title, summary, link
 * - FAQs: id, question, answer, link (null)
 */
class RetrievalCandidate implements JsonSerializable
{
    public function __construct(
        public readonly string $type, // product, article, faq
        public readonly int|string|null $id,
        public readonly string $title,
        public readonly string $summary,
        public readonly ?float $price = null,
        public readonly ?int $stock = null,
        public readonly ?string $unit = null,
        public readonly ?string $link = null,
        public readonly float $score = 0.0,
        public readonly array $extra = [],
    ) {}

    /**
     * Create a product candidate.
     * 
     * Output fields: id, title, price, stock, unit, summary, link
     * Link format: url('/produk/' . $product->product_id)
     */
    public static function fromProduct(object $product, float $score = 0.0): self
    {
        $productId = $product->product_id ?? $product->id ?? null;
        
        return new self(
            type: 'product',
            id: $productId,
            title: $product->product_name ?? '',
            summary: self::truncateText($product->description ?? '', 200),
            price: (float) ($product->price_per_unit ?? 0),
            stock: (int) ($product->stock ?? 0),
            unit: $product->unit ?? 'unit',
            link: $productId ? url('/produk/' . $productId) : null,
            score: $score,
            extra: [
                'minimum_purchase' => $product->minimum_purchase ?? null,
                'plant_type_id' => $product->plant_type_id ?? null,
                'plant_type_name' => $product->plantType->name ?? $product->plant_type_name ?? 'Lainnya',
            ],
        );
    }

    /**
     * Create an article candidate.
     * 
     * Output fields: id, title, summary, link
     * Link format: url('/artikel/' . $article->id)
     */
    public static function fromArticle(object $article, float $score = 0.0): self
    {
        // Articles table uses 'id' as primary key (not article_id)
        $articleId = $article->article_id ?? $article->id ?? null;
        
        return new self(
            type: 'article',
            id: $articleId,
            title: $article->headline ?? '',
            summary: self::truncateText($article->body ?? '', 200),
            link: $articleId ? url('/artikel/' . $articleId) : null,
            score: $score,
            extra: [
                'image_url' => $article->image ?? $article->image_url ?? null,
                'created_at' => $article->created_at ?? null,
            ],
        );
    }

    /**
     * Create a FAQ candidate.
     * 
     * Output fields: id, question, answer, link (always null)
     * FAQ does not have a link.
     */
    public static function fromFaq(object $faq, float $score = 0.0): self
    {
        return new self(
            type: 'faq',
            id: $faq->id ?? 0,
            title: $faq->question ?? '',
            summary: $faq->answer ?? '',
            link: null, // FAQ tidak punya link
            score: $score,
            extra: [
                'keywords' => $faq->keywords ?? null,
            ],
        );
    }

    /**
     * Truncate text to a specified length.
     */
    private static function truncateText(string $text, int $length): string
    {
        $text = strip_tags($text);
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . '...';
    }

    /**
     * Serialize to JSON with consistent field structure per type.
     * 
     * - Products: type, id, title, price, stock, unit, summary, link
     * - Articles: type, id, title, summary, link
     * - FAQs: type, id, question, answer, link (null)
     */
    public function jsonSerialize(): array
    {
        return match ($this->type) {
            'product' => [
                'type' => 'product',
                'id' => $this->id,
                'title' => $this->title,
                'price' => $this->price,
                'stock' => $this->stock,
                'unit' => $this->unit,
                'summary' => $this->summary,
                'link' => $this->link,
            ],
            'article' => [
                'type' => 'article',
                'id' => $this->id,
                'title' => $this->title,
                'summary' => $this->summary,
                'link' => $this->link,
            ],
            'faq' => [
                'type' => 'faq',
                'id' => $this->id,
                'question' => $this->title,
                'answer' => $this->summary,
                'link' => null,
            ],
            default => [
                'type' => $this->type,
                'id' => $this->id,
                'title' => $this->title,
                'summary' => $this->summary,
                'link' => $this->link,
            ],
        };
    }

    /**
     * Get the type of candidate.
     */
    public function getType(): string
    {
        return $this->type;
    }
}
