<?php

namespace App\Contracts\Search;

interface FaqRetrieverInterface
{
    /**
     * Ambil kandidat FAQ terurut berdasarkan relevansi terhadap query.
     *
     * @param  string  $query
     * @param  array<string, mixed>  $options
     * @return array{
     *     query: string,
     *     normalized_query: string,
     *     abstained: bool,
     *     results: array<int, array<string, mixed>>,
     *     timings_ms: array{db_ms: float, compute_ms: float, total_ms: float}
     * }
     */
    public function retrieve(string $query, array $options = []): array;
}
