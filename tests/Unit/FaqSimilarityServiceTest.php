<?php

namespace Tests\Unit;

use App\Services\FaqSimilarityService;
use PHPUnit\Framework\TestCase;

class FaqSimilarityServiceTest extends TestCase
{
    private FaqSimilarityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FaqSimilarityService();
    }

    public function test_belum_and_negation_words_are_not_removed_by_tokenizer(): void
    {
        $tok = $this->service->tokenizeText('Apakah mahasiswa yang belum melunasi UKT tidak boleh mengisi KRS?');

        $this->assertContains('belum', $tok['bm25_tokens']);
        $this->assertContains('tidak', $tok['bm25_tokens']);
        $this->assertContains('boleh', $tok['bm25_tokens']);
        $this->assertContains('tidak_boleh', $tok['protected']['negation_bigrams']);
        $this->assertContains('belum', $tok['protected']['aspects']);
    }

    public function test_term_frequency_is_preserved_for_bm25_and_deduplicated_for_dice(): void
    {
        $tok = $this->service->tokenizeText('Jadwal KRS reguler dan jadwal KRS batal tambah');

        $counts = array_count_values($tok['bm25_tokens']);
        $this->assertGreaterThanOrEqual(2, $counts['jadwal'] ?? 0);
        $this->assertGreaterThanOrEqual(2, $counts['krs'] ?? 0);
        $this->assertSame(count($tok['unique_tokens']), count(array_unique($tok['unique_tokens'])));
    }

    public function test_conservative_stemmer_protects_academic_terms_and_roots(): void
    {
        $this->assertSame('daftar', $this->service->stemConservative('pendaftaran'));
        $this->assertSame('aju', $this->service->stemConservative('mengajukan'));
        $this->assertSame('pinjam', $this->service->stemConservative('peminjaman'));

        // Kata yang tidak boleh rusak maknanya oleh pemotongan prefiks/sufiks agresif
        $this->assertSame('pengadilan', $this->service->stemConservative('pengadilan'));
        $this->assertSame('peradilan', $this->service->stemConservative('peradilan'));
        $this->assertSame('belajar', $this->service->stemConservative('belajar'));
        $this->assertSame('laboratorium', $this->service->stemConservative('laboratorium'));
        $this->assertSame('yudisium', $this->service->stemConservative('yudisium'));
        $this->assertSame('semester', $this->service->stemConservative('semester'));
    }

    public function test_boleh_is_not_equated_with_tidak_boleh(): void
    {
        $candidates = collect([
            [
                'id'           => 'FAQ-0003',
                'pertanyaan'   => 'Apakah mahasiswa boleh mengajukan cuti akademik pada semester 3?',
                'jawaban'      => 'Mahasiswa yang telah menempuh 2 semester awal boleh mengajukan cuti akademik.',
                'unit_id'      => 1,
                'unit_nama'    => 'Direktorat Akademik',
                'status'       => 'Terbit',
                'status_label' => 'Terbit',
                'version_id'   => 'FAQ-0003-V1',
                'tags'         => ['Cuti Akademik'],
                'alternatives' => [],
            ],
            [
                'id'           => 'FAQ-0044',
                'pertanyaan'   => 'Mengapa mahasiswa semester 1 tidak boleh mengajukan cuti akademik?',
                'jawaban'      => 'Mahasiswa baru semester 1 tidak boleh mengambil cuti reguler.',
                'unit_id'      => 1,
                'unit_nama'    => 'Direktorat Akademik',
                'status'       => 'Terbit',
                'status_label' => 'Terbit',
                'version_id'   => 'FAQ-0044-V1',
                'tags'         => ['Cuti Akademik'],
                'alternatives' => [],
            ],
        ]);

        // Deteksi duplikat internal: kalimat positif vs kalimat negatif pada semester yang sama TIDAK boleh exact atau high
        $dupCheck = $this->service->analyzeQuestion(
            'Apakah mahasiswa semester 1 boleh mengajukan cuti akademik?',
            null,
            20.0,
            null,
            collect([$candidates[1]])
        );

        $this->assertFalse($dupCheck['has_exact_match']);
        if (!empty($dupCheck['candidates'])) {
            $this->assertNotSame('exact', $dupCheck['candidates'][0]['level']);
            $this->assertNotSame('high', $dupCheck['candidates'][0]['level']);
            $this->assertNotEmpty($dupCheck['candidates'][0]['constraint_penalty']);
        }

        // Pencarian publik harus memilih FAQ yang sesuai polaritasnya
        $negSearch = $this->service->searchPublishedFaqs('mahasiswa semester 1 tidak boleh mengajukan cuti akademik', [
            'preloaded_candidates' => $candidates,
        ]);
        $this->assertFalse($negSearch['abstained']);
        $this->assertSame('FAQ-0044', $negSearch['results'][0]['id']);
    }

    public function test_wajib_is_not_equated_with_tidak_wajib(): void
    {
        $candidates = collect([
            [
                'id'           => 'FAQ-0032',
                'pertanyaan'   => 'Apakah karya Tugas Akhir FIK wajib didaftarkan HKI sebelum yudisium?',
                'jawaban'      => 'Karya terpilih difasilitasi pendaftaran HKI oleh fakultas.',
                'unit_id'      => 6,
                'unit_nama'    => 'Fakultas Industri Kreatif',
                'status'       => 'Terbit',
                'status_label' => 'Terbit',
                'version_id'   => 'FAQ-0032-V1',
                'tags'         => ['Tugas Akhir'],
                'alternatives' => [],
            ],
            [
                'id'           => 'FAQ-0047',
                'pertanyaan'   => 'Kapan karya mahasiswa FIK tidak wajib didaftarkan HKI?',
                'jawaban'      => 'Karya latihan mata kuliah reguler tidak wajib didaftarkan HKI.',
                'unit_id'      => 6,
                'unit_nama'    => 'Fakultas Industri Kreatif',
                'status'       => 'Terbit',
                'status_label' => 'Terbit',
                'version_id'   => 'FAQ-0047-V1',
                'tags'         => ['Tugas Akhir'],
                'alternatives' => [],
            ],
        ]);

        $resTidakWajib = $this->service->searchPublishedFaqs('karya fik yang tidak wajib didaftarkan hki', [
            'preloaded_candidates' => $candidates,
        ]);
        $this->assertSame('FAQ-0047', $resTidakWajib['results'][0]['id']);

        $resWajib = $this->service->searchPublishedFaqs('apakah karya tugas akhir fik wajib didaftarkan hki', [
            'preloaded_candidates' => $candidates,
        ]);
        $this->assertSame('FAQ-0032', $resWajib['results'][0]['id']);
    }

    public function test_numbers_sks_ipk_semester_and_degree_codes_are_distinguished(): void
    {
        $candidates = collect([
            [
                'id'           => 'FAQ-0011',
                'pertanyaan'   => 'Apa syarat pengajuan Tugas Akhir Fakultas Teknik minimal 120 SKS?',
                'jawaban'      => 'Minimal 120 SKS dengan IPK >= 2.00.',
                'unit_id'      => 2,
                'unit_nama'    => 'Fakultas Teknik',
                'status'       => 'Terbit',
                'status_label' => 'Terbit',
                'version_id'   => 'FAQ-0011-V1',
                'tags'         => ['Tugas Akhir'],
                'alternatives' => [],
            ],
            [
                'id'           => 'FAQ-0013',
                'pertanyaan'   => 'Apa syarat pengajuan Kerja Praktik Fakultas Teknik minimal 90 SKS?',
                'jawaban'      => 'Minimal lulus 90 SKS dan durasi 160 jam.',
                'unit_id'      => 2,
                'unit_nama'    => 'Fakultas Teknik',
                'status'       => 'Terbit',
                'status_label' => 'Terbit',
                'version_id'   => 'FAQ-0013-V1',
                'tags'         => ['Kerja Praktik'],
                'alternatives' => [],
            ],
        ]);

        $r90 = $this->service->searchPublishedFaqs('syarat minimal 90 sks fakultas teknik', [
            'preloaded_candidates' => $candidates,
        ]);
        $this->assertSame('FAQ-0013', $r90['results'][0]['id']);

        $r120 = $this->service->searchPublishedFaqs('syarat minimal 120 sks fakultas teknik', [
            'preloaded_candidates' => $candidates,
        ]);
        $this->assertSame('FAQ-0011', $r120['results'][0]['id']);
    }

    public function test_exact_match_is_distinct_from_near_duplicate(): void
    {
        $candidates = collect([
            [
                'id'           => 'FAQ-0008',
                'pertanyaan'   => 'Bagaimana prosedur pengurusan Kartu Tanda Mahasiswa (KTM) yang hilang atau rusak?',
                'jawaban'      => 'Lapor ke BAAK membawa surat kehilangan.',
                'unit_id'      => 1,
                'unit_nama'    => 'Direktorat Akademik',
                'status'       => 'Terbit',
                'status_label' => 'Terbit',
                'version_id'   => 'FAQ-0008-V1',
                'tags'         => [],
                'alternatives' => [],
            ],
        ]);

        // Parafrasa sangat mirip menggunakan singkatan/informal -> near-duplicate, BUKAN exact
        $near = $this->service->analyzeQuestion(
            'Gimana cara ngurus KTM hilang atau rusak?',
            null,
            30.0,
            null,
            $candidates
        );
        $this->assertFalse($near['has_exact_match']);
        $this->assertNull($near['exact_match']);
        $this->assertNotEmpty($near['candidates']);
        $this->assertFalse($near['candidates'][0]['is_exact']);
        $this->assertLessThan(100.0, $near['candidates'][0]['similarity']);

        // Identik secara normalisasi -> exact = true (100.0)
        $exact = $this->service->analyzeQuestion(
            'bagaimana prosedur pengurusan kartu tanda mahasiswa (ktm) yang hilang atau rusak',
            null,
            30.0,
            null,
            $candidates
        );
        $this->assertTrue($exact['has_exact_match']);
        $this->assertSame('FAQ-0008', $exact['exact_match']['id']);
        $this->assertSame(100.0, $exact['candidates'][0]['similarity']);
        $this->assertSame('exact', $exact['candidates'][0]['level']);
    }

    public function test_no_answer_query_abstains_instead_of_forcing_irrelevant_results(): void
    {
        $candidates = collect([
            [
                'id'           => 'FAQ-0001',
                'pertanyaan'   => 'Bagaimana alur pengisian KRS mahasiswa awal semester?',
                'jawaban'      => 'Melalui portal akademik sesuai jadwal perwalian.',
                'unit_id'      => 1,
                'unit_nama'    => 'Direktorat Akademik',
                'status'       => 'Terbit',
                'status_label' => 'Terbit',
                'version_id'   => 'FAQ-0001-V1',
                'tags'         => ['KRS'],
                'alternatives' => [],
            ],
            [
                'id'           => 'FAQ-0014',
                'pertanyaan'   => 'Bagaimana syarat pendaftaran Yudisium dan batas skor minimal TOEFL di Fakultas Teknik?',
                'jawaban'      => 'TOEFL minimal 475 dan Turnitin maksimal 20%.',
                'unit_id'      => 2,
                'unit_nama'    => 'Fakultas Teknik',
                'status'       => 'Terbit',
                'status_label' => 'Terbit',
                'version_id'   => 'FAQ-0014-V1',
                'tags'         => ['Yudisium'],
                'alternatives' => [],
            ],
        ]);

        $res = $this->service->searchPublishedFaqs('jadwal keberangkatan shuttle bus menuju stasiun gubeng', [
            'preloaded_candidates' => $candidates,
        ]);

        $this->assertTrue($res['abstained']);
        $this->assertSame([], $res['results']);
    }

    public function test_editing_existing_faq_excludes_itself_from_duplicate_candidates(): void
    {
        $candidates = collect([
            [
                'id'           => 'FAQ-0014',
                'pertanyaan'   => 'Bagaimana syarat pendaftaran Yudisium dan batas skor minimal TOEFL di Fakultas Teknik?',
                'jawaban'      => 'TOEFL minimal 475.',
                'unit_id'      => 2,
                'unit_nama'    => 'Fakultas Teknik',
                'status'       => 'Draf',
                'status_label' => 'Draf',
                'version_id'   => 'FAQ-0014-V1',
                'all_versions' => [
                    [
                        'id'           => 'FAQ-0014-V1',
                        'status'       => 'Draf',
                        'status_label' => 'Draf',
                        'jawaban'      => 'TOEFL minimal 475.',
                        'norm_jawaban' => $this->service->normalizeRaw('TOEFL minimal 475.'),
                    ],
                ],
            ],
        ]);

        $res = $this->service->analyzeQuestion(
            'Bagaimana syarat pendaftaran Yudisium dan batas skor minimal TOEFL di Fakultas Teknik?',
            'FAQ-0014',
            30.0,
            'TOEFL minimal 475.',
            $candidates
        );

        $this->assertFalse($res['has_exact_match']);
        $this->assertFalse($res['has_exact_draft_match']);
        $this->assertSame([], $res['candidates']);
    }
}
