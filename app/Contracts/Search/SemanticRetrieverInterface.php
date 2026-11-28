<?php

namespace App\Contracts\Search;

/**
 * Kontrak opsional untuk retriever berbasis semantic embedding di masa depan.
 * Memungkinkan penggabungan kandidat lexical (BM25) dan semantic melalui RRF
 * tanpa mengikat controller pada satu penyedia model/API tertentu.
 */
interface SemanticRetrieverInterface extends FaqRetrieverInterface
{
    /**
     * Menghasilkan vektor embedding untuk satu teks (saat indexing FAQ terbit atau query).
     *
     * @return float[]
     */
    public function embed(string $text): array;
}
