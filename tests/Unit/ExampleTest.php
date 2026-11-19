<?php

namespace Tests\Unit;

use App\Http\Controllers\PortalController;
use App\Models\Faq;
use App\Models\Tag;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_parse_nrp_extracts_fakultas_prodi_and_angkatan(): void
    {
        $parsed = PortalController::parseNrp('160423018');

        $this->assertNotNull($parsed);
        $this->assertSame('16', $parsed['kode_fakultas']);
        $this->assertSame('Fakultas Teknik', $parsed['nama_fakultas']);
        $this->assertSame('04', $parsed['kode_prodi']);
        $this->assertSame('23', $parsed['kode_angkatan']);
        $this->assertSame('2023', $parsed['tahun_angkatan']);
        $this->assertSame('018', $parsed['nomor_urut']);
        $this->assertSame('16-23', $parsed['session_key']);
    }

    public function test_rank_faqs_prioritizes_angkatan_stage_and_log_access(): void
    {
        $faqKrs = new Faq(['id' => 'FAQ-0001', 'pertanyaan' => 'Bagaimana cara pengisian KRS mahasiswa baru?']);
        $faqKrs->setRelation('tags', collect([new Tag(['nama' => 'KRS']), new Tag(['nama' => 'Fakultas Teknik'])]));

        $faqTa = new Faq(['id' => 'FAQ-0002', 'pertanyaan' => 'Bagaimana syarat pendaftaran sidang Tugas Akhir dan Wisuda Fakultas Teknik?']);
        $faqTa->setRelation('tags', collect([new Tag(['nama' => 'Tugas Akhir']), new Tag(['nama' => 'Wisuda']), new Tag(['nama' => 'Fakultas Teknik'])]));

        $faqHukum = new Faq(['id' => 'FAQ-0003', 'pertanyaan' => 'Bagaimana syarat skripsi Fakultas Hukum?']);
        $faqHukum->setRelation('tags', collect([new Tag(['nama' => 'Fakultas Hukum']), new Tag(['nama' => 'Tugas Akhir'])]));

        $logStats = collect([
            (object) ['faq_id' => 'FAQ-0002', 'session_key' => '16-23', 'jenis_interaksi' => 'view', 'keyword' => 'buka-faq', 'hasil' => 1, 'total_akses' => 3],
            (object) ['faq_id' => 'FAQ-0002', 'session_key' => '16-23', 'jenis_interaksi' => 'like', 'keyword' => 'feedback-membantu', 'hasil' => 1, 'total_akses' => 2],
            (object) ['faq_id' => 'FAQ-0001', 'session_key' => '16-26', 'jenis_interaksi' => 'dislike', 'keyword' => 'feedback-belum', 'hasil' => 0, 'total_akses' => 1],
        ]);

        // Mahasiswa Fakultas Teknik (16) Angkatan 2023 (23) Semester 7
        $ranked = PortalController::rankFaqsForStudent(
            collect([$faqKrs, $faqTa, $faqHukum]),
            '16',
            '23',
            7,
            $logStats
        );

        $this->assertSame('FAQ-0002', $ranked->first()->id);
        $this->assertSame(3, $ranked->first()->views_count);
        $this->assertSame(2, $ranked->first()->likes_count);
        $this->assertSame(0, $ranked->first()->dislikes_count);
        $this->assertSame(1, $ranked[1]->dislikes_count);
        $this->assertSame('FAQ-0003', $ranked->last()->id);
        $this->assertTrue($ranked->last()->is_other_fakultas);
    }
}
