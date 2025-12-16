<?php

namespace App\Services\Chatbot;

use App\Contracts\RetrievalServiceInterface;
use App\DTO\RetrievalCandidate;
use App\DTO\RouterOutput;
use App\Models\Article;
use App\Models\Faq;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

/**
 * Service for retrieving candidates from database with scoring and gating.
 * Uses heuristic-based relevance scoring.
 */
class RetrievalService implements RetrievalServiceInterface
{
    /**
     * Score thresholds for gating.
     */
    public const THRESHOLD_STRONG = 0.65;   // >= 0.65 → found
    public const THRESHOLD_MEDIUM = 0.45;   // 0.45-0.65 → need_clarification
    // < 0.45 → not_found / out_of_scope

    /**
     * Maximum candidates to return to Composer.
     */
    private const MAX_CANDIDATES = 5;

    /**
     * Field weights for scoring.
     */
    private const PRODUCT_WEIGHTS = [
        'product_name' => 3.0,      // Primary field - highest weight
        'description' => 1.5,       // Secondary field
        'plant_type_name' => 2.0,   // Important for category matching
        'specifications' => 1.0,    // Parsed specifications
    ];

    private const ARTICLE_WEIGHTS = [
        'headline' => 3.0,          // Primary field
        'body' => 1.0,              // Body excerpt
    ];

    private const FAQ_WEIGHTS = [
        'question' => 3.0,          // Primary field
        'keywords' => 2.5,          // Keywords are highly relevant
        'answer' => 1.0,            // Answer content
    ];

    /**
     * Indonesian stopwords for tokenization.
     */
    private const STOPWORDS = [
        // Conjunctions & prepositions
        'dan', 'atau', 'yang', 'di', 'ke', 'dari', 'untuk', 'dengan', 'pada', 'dalam',
        'oleh', 'sebagai', 'karena', 'jika', 'bila', 'agar', 'supaya', 'serta', 'maupun',
        // Pronouns
        'saya', 'aku', 'kamu', 'anda', 'dia', 'ia', 'kami', 'kita', 'mereka', 'ini', 'itu',
        // Verbs (common)
        'adalah', 'ada', 'akan', 'bisa', 'dapat', 'harus', 'sudah', 'telah', 'sedang',
        'belum', 'tidak', 'bukan', 'jangan', 'boleh', 'perlu', 'mau', 'ingin',
        // Question words
        'apa', 'siapa', 'dimana', 'kapan', 'mengapa', 'kenapa', 'bagaimana', 'berapa',
        'mana', 'gimana',
        // Common chatbot words
        'tolong', 'mohon', 'cari', 'carikan', 'kasih', 'tahu', 'info', 'dong', 'ya',
        'nih', 'sih', 'deh', 'lah', 'kok', 'kan', 'banget', 'sekali',
        // Articles & others
        'sebuah', 'suatu', 'para', 'sang', 'si', 'nya', 'mu', 'ku',
    ];

    /**
     * Result of retrieval with gating info.
     */
    private array $retrievalResult = [
        'candidates' => [],
        'top_score' => 0.0,
        'gate_status' => 'not_found', // found, need_clarification, not_found
        'clarifying_question' => null,
    ];

    /**
     * {@inheritdoc}
     */
    public function retrieve(RouterOutput $routerOutput, int $limit = 5): array
    {
        $this->resetResult();
        
        $candidates = [];
        $query = $routerOutput->filters['query'] ?? $routerOutput->filters['search_query'] ?? '';
        $filters = $routerOutput->filters;

        foreach ($routerOutput->sources as $source) {
            $sourceCandidates = match ($source) {
                'products' => $this->searchProducts($query, $filters, $limit * 2), // Get more, filter later
                'articles' => $this->searchArticles($query, $filters, $limit * 2),
                'faqs' => $this->searchFaqs($query, $filters, $limit * 2),
                default => [],
            };
            $candidates = array_merge($candidates, $sourceCandidates);
        }

        // Sort by score descending
        usort($candidates, fn($a, $b) => $b->score <=> $a->score);

        // Take only top candidates
        $candidates = array_slice($candidates, 0, self::MAX_CANDIDATES);

        // Determine gating based on top score
        $topScore = !empty($candidates) ? $candidates[0]->score : 0.0;
        $this->applyGating($topScore, $query, $routerOutput);

        $this->retrievalResult['candidates'] = $candidates;
        $this->retrievalResult['top_score'] = $topScore;

        Log::debug('Retrieval results with gating', [
            'sources' => $routerOutput->sources,
            'query' => $query,
            'total_candidates' => count($candidates),
            'top_score' => $topScore,
            'gate_status' => $this->retrievalResult['gate_status'],
        ]);

        return $candidates;
    }

    /**
     * Get the retrieval result with gating information.
     */
    public function getRetrievalResult(): array
    {
        return $this->retrievalResult;
    }

    /**
     * Get the gate status from last retrieval.
     */
    public function getGateStatus(): string
    {
        return $this->retrievalResult['gate_status'];
    }

    /**
     * Get clarifying question if needed.
     */
    public function getClarifyingQuestion(): ?string
    {
        return $this->retrievalResult['clarifying_question'];
    }

    /**
     * {@inheritdoc}
     */
    public function searchProducts(string $query, array $filters = [], int $limit = 5): array
    {
        $searchQuery = Product::query()->with('plantType');

        // Build search conditions
        $searchTerms = $this->tokenize($query);

        if (!empty($searchTerms)) {
            $searchQuery->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->orWhere('product_name', 'LIKE', "%{$term}%")
                      ->orWhere('description', 'LIKE', "%{$term}%");
                }
            });
        }

        // Apply filters
        $this->applyProductFilters($searchQuery, $filters);

        // Only get products with stock > 0
        $searchQuery->where('stock', '>', 0);

        $products = $searchQuery->limit($limit)->get();

        // If no results with search terms, try broader search
        if ($products->isEmpty() && !empty($filters)) {
            $searchQuery = Product::query()->with('plantType');
            $this->applyProductFilters($searchQuery, $filters);
            $searchQuery->where('stock', '>', 0);
            $products = $searchQuery->limit($limit)->get();
        }

        return $products->map(function ($product) use ($query, $filters) {
            $score = $this->calculateProductScore($product, $query, $filters);
            return RetrievalCandidate::fromProduct($product, $score);
        })->sortByDesc('score')->values()->all();
    }

    /**
     * {@inheritdoc}
     */
    public function searchArticles(string $query, array $filters = [], int $limit = 5): array
    {
        if (empty($query)) {
            return [];
        }

        $searchQuery = Article::query();
        $searchTerms = $this->tokenize($query);

        if (!empty($searchTerms)) {
            $searchQuery->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->orWhere('headline', 'LIKE', "%{$term}%")
                      ->orWhere('body', 'LIKE', "%{$term}%");
                }
            });
        }

        $searchQuery->orderBy('created_at', 'desc');
        $articles = $searchQuery->limit($limit)->get();

        return $articles->map(function ($article) use ($query) {
            $score = $this->calculateArticleScore($article, $query);
            return RetrievalCandidate::fromArticle($article, $score);
        })->sortByDesc('score')->values()->all();
    }

    /**
     * {@inheritdoc}
     */
    public function searchFaqs(string $query, array $filters = [], int $limit = 5): array
    {
        if (empty($query)) {
            return [];
        }

        $searchQuery = Faq::query();
        $searchTerms = $this->tokenize($query);

        if (!empty($searchTerms)) {
            $searchQuery->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->orWhere('keywords', 'LIKE', "%{$term}%")
                      ->orWhere('question', 'LIKE', "%{$term}%")
                      ->orWhere('answer', 'LIKE', "%{$term}%");
                }
            });
        }

        $faqs = $searchQuery->limit($limit)->get();

        return $faqs->map(function ($faq) use ($query) {
            $score = $this->calculateFaqScore($faq, $query);
            return RetrievalCandidate::fromFaq($faq, $score);
        })->sortByDesc('score')->values()->all();
    }

    /**
     * Tokenize text: lowercase, remove stopwords, split whitespace.
     * 
     * @param string $text Input text
     * @param bool $removeStopwords Whether to remove stopwords
     * @return array Array of tokens
     */
    public function tokenize(string $text, bool $removeStopwords = true): array
    {
        // Lowercase
        $text = strtolower(trim($text));

        // Remove punctuation except hyphens within words
        $text = preg_replace('/[^\w\s\-]/u', ' ', $text);

        // Split by whitespace
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        // Filter
        $tokens = array_filter($words, function ($word) use ($removeStopwords) {
            // Minimum length
            if (strlen($word) < 2) {
                return false;
            }

            // Remove stopwords if enabled
            if ($removeStopwords && in_array($word, self::STOPWORDS)) {
                return false;
            }

            // Remove pure numbers (keep alphanumeric like "f1", "10kg")
            if (preg_match('/^\d+$/', $word)) {
                return false;
            }

            return true;
        });

        return array_values($tokens);
    }

    /**
     * Calculate relevance score for a product.
     */
    private function calculateProductScore(object $product, string $query, array $filters): float
    {
        $queryTokens = $this->tokenize($query);
        
        if (empty($queryTokens) && empty($filters)) {
            return 0.3; // Base score when no query
        }

        $totalScore = 0;
        $totalWeight = 0;

        // Score product_name (highest weight)
        $nameScore = $this->calculateFieldScore(
            $product->product_name ?? '',
            $queryTokens
        );
        $totalScore += $nameScore * self::PRODUCT_WEIGHTS['product_name'];
        $totalWeight += self::PRODUCT_WEIGHTS['product_name'];

        // Score description
        $descScore = $this->calculateFieldScore(
            $product->description ?? '',
            $queryTokens
        );
        $totalScore += $descScore * self::PRODUCT_WEIGHTS['description'];
        $totalWeight += self::PRODUCT_WEIGHTS['description'];

        // Score plant type name if available
        if ($product->plantType) {
            $plantTypeScore = $this->calculateFieldScore(
                $product->plantType->name ?? '',
                $queryTokens
            );
            $totalScore += $plantTypeScore * self::PRODUCT_WEIGHTS['plant_type_name'];
            $totalWeight += self::PRODUCT_WEIGHTS['plant_type_name'];

            // Bonus if plant_type filter matches
            if (!empty($filters['plant_type'])) {
                $filterPlantType = strtolower($filters['plant_type']);
                $productPlantType = strtolower($product->plantType->name ?? '');
                if (str_contains($productPlantType, $filterPlantType) || 
                    str_contains($filterPlantType, $productPlantType)) {
                    $totalScore += 0.5 * self::PRODUCT_WEIGHTS['plant_type_name'];
                }
            }
        }

        // Parse and score specifications from description
        $specScore = $this->scoreProductSpecifications($product, $filters);
        $totalScore += $specScore * self::PRODUCT_WEIGHTS['specifications'];
        $totalWeight += self::PRODUCT_WEIGHTS['specifications'];

        // Calculate weighted average
        $score = $totalWeight > 0 ? $totalScore / $totalWeight : 0;

        // Boost for exact phrase match in product name
        if (!empty($query) && str_contains(strtolower($product->product_name ?? ''), strtolower($query))) {
            $score = min(1.0, $score + 0.25);
        }

        return round(min(1.0, $score), 2);
    }

    /**
     * Calculate relevance score for an article.
     */
    private function calculateArticleScore(object $article, string $query): float
    {
        $queryTokens = $this->tokenize($query);
        
        if (empty($queryTokens)) {
            return 0.3;
        }

        $totalScore = 0;
        $totalWeight = 0;

        // Score headline (highest weight)
        $headlineScore = $this->calculateFieldScore(
            $article->headline ?? '',
            $queryTokens
        );
        $totalScore += $headlineScore * self::ARTICLE_WEIGHTS['headline'];
        $totalWeight += self::ARTICLE_WEIGHTS['headline'];

        // Score body excerpt (first 500 chars for performance)
        $bodyExcerpt = substr($article->body ?? '', 0, 500);
        $bodyScore = $this->calculateFieldScore($bodyExcerpt, $queryTokens);
        $totalScore += $bodyScore * self::ARTICLE_WEIGHTS['body'];
        $totalWeight += self::ARTICLE_WEIGHTS['body'];

        $score = $totalWeight > 0 ? $totalScore / $totalWeight : 0;

        // Boost for exact phrase in headline
        if (str_contains(strtolower($article->headline ?? ''), strtolower($query))) {
            $score = min(1.0, $score + 0.25);
        }

        return round(min(1.0, $score), 2);
    }

    /**
     * Calculate relevance score for a FAQ.
     */
    private function calculateFaqScore(object $faq, string $query): float
    {
        $queryTokens = $this->tokenize($query);
        
        if (empty($queryTokens)) {
            return 0.3;
        }

        $totalScore = 0;
        $totalWeight = 0;

        // Score question (highest weight)
        $questionScore = $this->calculateFieldScore(
            $faq->question ?? '',
            $queryTokens
        );
        $totalScore += $questionScore * self::FAQ_WEIGHTS['question'];
        $totalWeight += self::FAQ_WEIGHTS['question'];

        // Score keywords (high weight - comma separated)
        $keywordsScore = $this->calculateFieldScore(
            $faq->keywords ?? '',
            $queryTokens
        );
        $totalScore += $keywordsScore * self::FAQ_WEIGHTS['keywords'];
        $totalWeight += self::FAQ_WEIGHTS['keywords'];

        // Score answer
        $answerScore = $this->calculateFieldScore(
            $faq->answer ?? '',
            $queryTokens
        );
        $totalScore += $answerScore * self::FAQ_WEIGHTS['answer'];
        $totalWeight += self::FAQ_WEIGHTS['answer'];

        $score = $totalWeight > 0 ? $totalScore / $totalWeight : 0;

        // Boost for keyword exact match
        $keywords = array_map('trim', explode(',', strtolower($faq->keywords ?? '')));
        foreach ($queryTokens as $token) {
            if (in_array($token, $keywords)) {
                $score = min(1.0, $score + 0.15);
            }
        }

        return round(min(1.0, $score), 2);
    }

    /**
     * Calculate score for a single field against query tokens.
     */
    private function calculateFieldScore(string $fieldValue, array $queryTokens): float
    {
        if (empty($queryTokens) || empty($fieldValue)) {
            return 0;
        }

        $fieldTokens = $this->tokenize($fieldValue, false); // Keep stopwords in field
        $fieldLower = strtolower($fieldValue);

        $matchedTokens = 0;
        $partialMatches = 0;

        foreach ($queryTokens as $queryToken) {
            // Check exact token match
            if (in_array($queryToken, $fieldTokens)) {
                $matchedTokens++;
            }
            // Check partial/substring match
            elseif (str_contains($fieldLower, $queryToken)) {
                $partialMatches++;
            }
            // Check for stemming-like partial match (e.g., "tomat" matches "tomato")
            else {
                foreach ($fieldTokens as $fieldToken) {
                    if (strlen($queryToken) >= 3 && strlen($fieldToken) >= 3) {
                        // Check if one contains the other (simple stemming)
                        if (str_contains($fieldToken, $queryToken) || str_contains($queryToken, $fieldToken)) {
                            $partialMatches += 0.5;
                            break;
                        }
                    }
                }
            }
        }

        $totalQueryTokens = count($queryTokens);
        
        // Full match = 1.0, Partial match = 0.5
        $score = ($matchedTokens + ($partialMatches * 0.5)) / $totalQueryTokens;

        return min(1.0, $score);
    }

    /**
     * Score product specifications from description/filters.
     */
    private function scoreProductSpecifications(object $product, array $filters): float
    {
        $score = 0;
        $matchCount = 0;
        $totalFilters = 0;

        // Check altitude filter with mapping
        if (!empty($filters['altitude'])) {
            $totalFilters++;
            $description = $product->description ?? '';
            $altitudeKeywords = $this->getAltitudeKeywords($filters['altitude']);
            
            foreach ($altitudeKeywords as $keyword) {
                if (stripos($description, $keyword) !== false) {
                    $matchCount++;
                    break; // Found match, no need to check further
                }
            }
        }

        // Check harvest range
        if (!empty($filters['harvest_range_hst'])) {
            $totalFilters++;
            $description = strtolower($product->description ?? '');
            
            // Look for HST patterns
            if (preg_match('/(\d+)\s*[-–]\s*(\d+)\s*hst/i', $description) ||
                str_contains($description, 'hst') ||
                str_contains($description, 'hari setelah tanam')) {
                $matchCount++;
            }
        }

        // Check unit filter
        if (!empty($filters['unit'])) {
            $totalFilters++;
            $productUnit = strtolower($product->unit ?? '');
            $filterUnit = strtolower($filters['unit']);
            
            if ($productUnit === $filterUnit || str_contains($productUnit, $filterUnit)) {
                $matchCount++;
            }
        }

        // Check price range
        if (!empty($filters['price_min']) || !empty($filters['price_max'])) {
            $totalFilters++;
            $price = (float) $product->price_per_unit;
            $inRange = true;
            
            if (!empty($filters['price_min']) && $price < $filters['price_min']) {
                $inRange = false;
            }
            if (!empty($filters['price_max']) && $price > $filters['price_max']) {
                $inRange = false;
            }
            
            if ($inRange) {
                $matchCount++;
            }
        }

        return $totalFilters > 0 ? $matchCount / $totalFilters : 0.5;
    }

    /**
     * Apply product filters to query builder.
     */
    private function applyProductFilters($query, array $filters): void
    {
        if (!empty($filters['product_name'])) {
            $query->where('product_name', 'LIKE', "%{$filters['product_name']}%");
        }

        if (!empty($filters['price_min'])) {
            $query->where('price_per_unit', '>=', $filters['price_min']);
        }

        if (!empty($filters['price_max'])) {
            $query->where('price_per_unit', '<=', $filters['price_max']);
        }

        if (!empty($filters['plant_type_id'])) {
            $query->where('plant_type_id', $filters['plant_type_id']);
        }

        // Search by plant type name if provided
        if (!empty($filters['plant_type'])) {
            $query->whereHas('plantType', function ($q) use ($filters) {
                $q->where('name', 'LIKE', "%{$filters['plant_type']}%");
            });
        }

        // Search altitude in description with mapping
        if (!empty($filters['altitude'])) {
            $altitudeKeywords = $this->getAltitudeKeywords($filters['altitude']);
            
            $query->where(function ($q) use ($altitudeKeywords) {
                foreach ($altitudeKeywords as $keyword) {
                    $q->orWhere('description', 'LIKE', "%{$keyword}%");
                }
            });
        }
    }

    /**
     * Get altitude keywords for searching in product descriptions.
     * Maps user-friendly terms to terms used in product descriptions.
     */
    private function getAltitudeKeywords(string $altitude): array
    {
        $altitude = strtolower(trim($altitude));
        
        // Mapping altitude to search keywords based on ProductSeeder descriptions
        // Format in descriptions: "Rekomendasi Dataran: Rendah (mdpl)"
        $mapping = [
            // Dataran Rendah
            'rendah' => ['Dataran: Rendah', 'Rendah (mdpl)', 'Rendah–Menengah', 'dataran rendah', 'lowland'],
            'pesisir' => ['Dataran: Rendah', 'Rendah (mdpl)', 'Rendah–Menengah', 'dataran rendah'],
            'pantai' => ['Dataran: Rendah', 'Rendah (mdpl)', 'Rendah–Menengah', 'dataran rendah'],
            'panas' => ['Dataran: Rendah', 'Rendah (mdpl)', 'dataran rendah'],
            'lowland' => ['Dataran: Rendah', 'Rendah (mdpl)', 'dataran rendah'],
            
            // Dataran Menengah
            'menengah' => ['Dataran: Menengah', 'Menengah (mdpl)', 'Rendah–Menengah', 'Menengah–Tinggi', 'dataran menengah'],
            'sedang' => ['Dataran: Menengah', 'Menengah (mdpl)', 'dataran menengah'],
            'bukit' => ['Dataran: Menengah', 'Menengah (mdpl)', 'dataran menengah'],
            'perbukitan' => ['Dataran: Menengah', 'Menengah (mdpl)', 'dataran menengah'],
            
            // Dataran Tinggi
            'tinggi' => ['Dataran: Tinggi', 'Tinggi (mdpl)', 'Menengah–Tinggi', 'dataran tinggi', 'highland'],
            'pegunungan' => ['Dataran: Tinggi', 'Tinggi (mdpl)', 'Menengah–Tinggi', 'dataran tinggi'],
            'gunung' => ['Dataran: Tinggi', 'Tinggi (mdpl)', 'dataran tinggi'],
            'sejuk' => ['Dataran: Tinggi', 'Tinggi (mdpl)', 'dataran tinggi'],
            'highland' => ['Dataran: Tinggi', 'Tinggi (mdpl)', 'dataran tinggi'],
        ];
        
        // Check for exact match first
        if (isset($mapping[$altitude])) {
            return $mapping[$altitude];
        }
        
        // Check if altitude contains any of the keys
        foreach ($mapping as $key => $keywords) {
            if (str_contains($altitude, $key)) {
                return $keywords;
            }
        }
        
        // Fallback: return the altitude as-is
        return [$altitude, str_replace(' ', '', $altitude)];
    }

    /**
     * Apply gating based on top score.
     */
    private function applyGating(float $topScore, string $query, RouterOutput $routerOutput): void
    {
        if ($topScore >= self::THRESHOLD_STRONG) {
            $this->retrievalResult['gate_status'] = 'found';
            $this->retrievalResult['clarifying_question'] = null;
        } elseif ($topScore >= self::THRESHOLD_MEDIUM) {
            $this->retrievalResult['gate_status'] = 'need_clarification';
            $this->retrievalResult['clarifying_question'] = $this->generateClarifyingQuestion($query, $routerOutput);
        } else {
            // Check if it's out_of_scope from router or just not found
            if ($routerOutput->intent === 'out_of_scope') {
                $this->retrievalResult['gate_status'] = 'out_of_scope';
            } else {
                $this->retrievalResult['gate_status'] = 'not_found';
            }
            $this->retrievalResult['clarifying_question'] = null;
        }
    }

    /**
     * Generate a clarifying question based on context.
     */
    private function generateClarifyingQuestion(string $query, RouterOutput $routerOutput): string
    {
        $intent = $routerOutput->intent;
        $sources = $routerOutput->sources;

        // Context-aware clarifying questions
        if (in_array('products', $sources)) {
            if (empty($query)) {
                return "Mohon maaf, benih jenis apa yang Anda cari? (contoh: tomat, cabai, bayam, dll)";
            }
            
            $filters = $routerOutput->filters;
            if (empty($filters['plant_type'])) {
                return "Apakah Anda mencari benih {$query} untuk jenis tanaman tertentu? (sayuran, buah, hias, dll)";
            }
            
            return "Saya menemukan beberapa hasil untuk \"{$query}\". Bisa diperjelas spesifikasinya? (varietas, ketinggian tanam, atau rentang harga)";
        }

        if (in_array('articles', $sources)) {
            return "Informasi budidaya seperti apa yang Anda cari? (cara menanam, perawatan, pemupukan, pengendalian hama, dll)";
        }

        if (in_array('faqs', $sources)) {
            return "Bisa diperjelas pertanyaan Anda? Apakah tentang cara pemesanan, pembayaran, atau pengiriman?";
        }

        return "Mohon maaf, bisa diperjelas apa yang Anda cari?";
    }

    /**
     * Reset retrieval result.
     */
    private function resetResult(): void
    {
        $this->retrievalResult = [
            'candidates' => [],
            'top_score' => 0.0,
            'gate_status' => 'not_found',
            'clarifying_question' => null,
        ];
    }

    /**
     * Check if score meets strong threshold.
     */
    public static function isStrongMatch(float $score): bool
    {
        return $score >= self::THRESHOLD_STRONG;
    }

    /**
     * Check if score meets medium threshold.
     */
    public static function isMediumMatch(float $score): bool
    {
        return $score >= self::THRESHOLD_MEDIUM && $score < self::THRESHOLD_STRONG;
    }

    /**
     * Check if score is weak.
     */
    public static function isWeakMatch(float $score): bool
    {
        return $score < self::THRESHOLD_MEDIUM;
    }

    /**
     * Future: Search using embeddings/semantic similarity.
     */
    protected function semanticSearch(string $query, string $collection, int $limit = 5): array
    {
        // TODO: Implement when adding embedding support
        // 1. Get embedding for query using embedding model
        // 2. Query vector database (pgvector, pinecone, etc.)
        // 3. Return candidates with cosine similarity scores
        
        throw new \RuntimeException('Semantic search not yet implemented');
    }
}
