<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Services\FaqSimilarityService;

$datasetPath = __DIR__ . '/../tests/Fixtures/faq_benchmark_dataset.json';
$dataset = json_decode((string) file_get_contents($datasetPath), true, 512, JSON_THROW_ON_ERROR);

$rawCorpus = collect($dataset['corpus']);
$publishedCorpus = $rawCorpus
    ->filter(fn (array $c) => strtolower((string) $c['status']) === 'terbit')
    ->map(function (array $c) {
        $c['status_label'] = 'Terbit';
        $c['version_id'] = $c['id'] . '-V1';
        return $c;
    })
    ->values();

$queries = $dataset['queries'];

$configs = [
    'Baseline (Legacy Cosine+Dice+Trigram)' => [
        'strategy'  => FaqSimilarityService::STRATEGY_LEGACY,
        'threshold' => 30.0,
    ],
    'Config A (BM25 Only)' => [
        'strategy'  => FaqSimilarityService::STRATEGY_BM25,
        'threshold' => 32.0,
    ],
    'Config B (BM25 + Trigram RRF)' => [
        'strategy'  => FaqSimilarityService::STRATEGY_BM25_TRIGRAM,
        'threshold' => 32.0,
    ],
    'Config C (BM25 + Token Dice + Trigram RRF)' => [
        'strategy'  => FaqSimilarityService::STRATEGY_BM25_DICE_TRIGRAM,
        'threshold' => 32.0,
    ],
];

function percentile(array $sortedValues, float $p): float
{
    if (empty($sortedValues)) {
        return 0.0;
    }
    $idx = ($p / 100.0) * (count($sortedValues) - 1);
    $lo = (int) floor($idx);
    $hi = (int) ceil($idx);
    if ($lo === $hi) {
        return (float) $sortedValues[$lo];
    }
    $w = $idx - $lo;
    return (float) ($sortedValues[$lo] * (1.0 - $w) + $sortedValues[$hi] * $w);
}

$summaryRows = [];
$detailedErrors = [];

foreach ($configs as $label => $cfg) {
    $service = new FaqSimilarityService($cfg['strategy']);

    $answerableCount = 0;
    $hitAt1 = 0;
    $hitAt5 = 0;
    $mrrSum = 0.0;
    $precisionAt5Sum = 0.0;

    $noAnswerCount = 0;
    $noAnswerCorrect = 0;

    $negNumCount = 0;
    $negNumHitAt1 = 0;

    $latencies = [];
    $misses = [];

    // Warm-up 1x
    $service->searchPublishedFaqs('krs', [
        'strategy'             => $cfg['strategy'],
        'threshold'            => $cfg['threshold'],
        'limit'                => 5,
        'preloaded_candidates' => $publishedCorpus,
    ]);

    foreach ($queries as $qItem) {
        $qText = (string) $qItem['query'];
        $expected = (string) $qItem['expected_id'];
        $cat = (string) $qItem['category'];

        $t0 = hrtime(true);
        $res = $service->searchPublishedFaqs($qText, [
            'strategy'             => $cfg['strategy'],
            'threshold'            => $cfg['threshold'],
            'limit'                => 5,
            'preloaded_candidates' => $publishedCorpus,
        ]);
        $latencies[] = (hrtime(true) - $t0) / 1e6;

        $topIds = array_map(fn ($r) => (string) $r['id'], $res['results']);

        if ($expected === 'no_answer') {
            $noAnswerCount++;
            if (empty($topIds)) {
                $noAnswerCorrect++;
            } else {
                $misses[] = "{$qItem['id']} ({$cat}): expected no_answer, got {$topIds[0]} ({$res['results'][0]['similarity']}%)";
            }
            continue;
        }

        $answerableCount++;
        $rankPos = array_search($expected, $topIds, true);
        $isHit1 = ($rankPos === 0);
        $isHit5 = ($rankPos !== false);

        if ($isHit1) {
            $hitAt1++;
        } else {
            $gotStr = empty($topIds) ? 'ABSTAIN' : ($topIds[0] . ' [' . ($res['results'][0]['similarity'] ?? 0) . '%]');
            $misses[] = "{$qItem['id']} ({$cat}): expected {$expected}, got {$gotStr}";
        }

        if ($isHit5) {
            $hitAt5++;
            $mrrSum += 1.0 / (((int) $rankPos) + 1);
            // Precision@5 dengan 1 dokumen ground-truth relevan per query:
            // proporsi hasil relevan di antara kandidat yang dikembalikan (atau 1/min(5, returned))
            $returnedCount = max(1, min(5, count($topIds)));
            $precisionAt5Sum += 1.0 / $returnedCount;
        }

        if (in_array($cat, ['negation_modality', 'numeric_code'], true)) {
            $negNumCount++;
            if ($isHit1) {
                $negNumHitAt1++;
            }
        }
    }

    sort($latencies);
    $noAnsAcc = $noAnswerCount > 0 ? ($noAnswerCorrect / $noAnswerCount) * 100.0 : 0.0;
    $far = 100.0 - $noAnsAcc;

    $summaryRows[$label] = [
        'strategy'          => $cfg['strategy'],
        'threshold'         => $cfg['threshold'],
        'recall_at_1'       => round(($hitAt1 / max(1, $answerableCount)) * 100.0, 2),
        'recall_at_5'       => round(($hitAt5 / max(1, $answerableCount)) * 100.0, 2),
        'mrr_at_5'          => round($mrrSum / max(1, $answerableCount), 4),
        'precision_at_5'    => round(($precisionAt5Sum / max(1, $answerableCount)) * 100.0, 2),
        'neg_num_acc_at_1'  => round(($negNumHitAt1 / max(1, $negNumCount)) * 100.0, 2),
        'no_answer_acc'     => round($noAnsAcc, 2),
        'false_accept_rate' => round($far, 2),
        'p50_ms'            => round(percentile($latencies, 50.0), 3),
        'p95_ms'            => round(percentile($latencies, 95.0), 3),
    ];
    $detailedErrors[$label] = $misses;
}

echo "=== BENCHMARK PENCARIAN FAQ KMS UBAYA ===\n";
echo sprintf(
    "Total Korpus: %d FAQ (%d Terbit, %d Non-Terbit) | Total Query: %d (64 Answerable + 8 No-Answer)\n\n",
    $rawCorpus->count(),
    $publishedCorpus->count(),
    $rawCorpus->count() - $publishedCorpus->count(),
    count($queries)
);

echo "| Konfigurasi | Recall@1 | Recall@5 | MRR@5 | Precision@5 | Negasi&Angka@1 | No-Answer Acc | FAR | P50 (ms) | P95 (ms) |\n";
echo "|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|\n";
foreach ($summaryRows as $label => $m) {
    echo sprintf(
        "| %s | %.2f%% | %.2f%% | %.4f | %.2f%% | %.2f%% | %.2f%% | %.2f%% | %.3f | %.3f |\n",
        $label,
        $m['recall_at_1'],
        $m['recall_at_5'],
        $m['mrr_at_5'],
        $m['precision_at_5'],
        $negNumAcc = $m['neg_num_acc_at_1'],
        $m['no_answer_acc'],
        $m['false_accept_rate'],
        $m['p50_ms'],
        $m['p95_ms']
    );
}

echo "\n--- Detail Misses per Konfigurasi ---\n";
foreach ($detailedErrors as $label => $errs) {
    echo "\n[{$label}] (" . count($errs) . " miss):\n";
    foreach (array_slice($errs, 0, 12) as $e) {
        echo "  - {$e}\n";
    }
}

if (in_array('--live-db', $argv ?? [], true)) {
    $app = require __DIR__ . '/../bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    \Illuminate\Support\Facades\Cache::flush();

    $svc = new FaqSimilarityService();
    $cold = $svc->searchPublishedFaqs('Berapa skor minimal toefl');
    $warm = $svc->searchPublishedFaqs('gimana cara nyicil ukt');
    $internal = $svc->analyzeQuestion('Bagaimana syarat pendaftaran Yudisium dan batas skor minimal TOEFL di Fakultas Teknik?');

    echo "\n=== PENGUKURAN WAKTU LIVE DATABASE MYSQL ===\n";
    echo sprintf(
        "Cold Public Search  -> Kandidat Terbit: %d | db_ms=%.3f | compute_ms=%.3f | total_ms=%.3f | top=%s\n",
        $svc->getPublishedCandidateFaqs()->count(),
        $cold['timings_ms']['db_ms'],
        $cold['timings_ms']['compute_ms'],
        $cold['timings_ms']['total_ms'],
        $cold['results'][0]['id'] ?? 'ABSTAIN'
    );
    echo sprintf(
        "Warm Public Search  -> db_ms=%.3f | compute_ms=%.3f | total_ms=%.3f | top=%s\n",
        $warm['timings_ms']['db_ms'],
        $warm['timings_ms']['compute_ms'],
        $warm['timings_ms']['total_ms'],
        $warm['results'][0]['id'] ?? 'ABSTAIN'
    );
    echo sprintf(
        "Internal Dup Check  -> Kandidat Internal: %d | db_ms=%.3f | compute_ms=%.3f | total_ms=%.3f | exact=%s\n",
        $svc->getCandidateFaqs()->count(),
        $internal['timings_ms']['db_ms'],
        $internal['timings_ms']['compute_ms'],
        $internal['timings_ms']['total_ms'],
        $internal['has_exact_match'] ? 'true' : 'false'
    );
}

