<?php

namespace App\Contracts;

use App\DTO\RouterOutput;

/**
 * Interface for retrieval service implementations.
 * Allows easy swapping between keyword search and semantic/embedding search.
 */
interface RetrievalServiceInterface
{
    /**
     * Retrieve candidates from the database based on router output.
     *
     * @param RouterOutput $routerOutput Output from the router service
     * @param int $limit Maximum number of candidates per source
     * @return array<\App\DTO\RetrievalCandidate>
     */
    public function retrieve(RouterOutput $routerOutput, int $limit = 5): array;

    /**
     * Search for products matching the query and filters.
     *
     * @param string $query Search query
     * @param array $filters Filters to apply
     * @param int $limit Maximum results
     * @return array<\App\DTO\RetrievalCandidate>
     */
    public function searchProducts(string $query, array $filters = [], int $limit = 5): array;

    /**
     * Search for articles matching the query.
     *
     * @param string $query Search query
     * @param array $filters Filters to apply
     * @param int $limit Maximum results
     * @return array<\App\DTO\RetrievalCandidate>
     */
    public function searchArticles(string $query, array $filters = [], int $limit = 5): array;

    /**
     * Search for FAQs matching the query.
     *
     * @param string $query Search query
     * @param array $filters Filters to apply
     * @param int $limit Maximum results
     * @return array<\App\DTO\RetrievalCandidate>
     */
    public function searchFaqs(string $query, array $filters = [], int $limit = 5): array;
}
