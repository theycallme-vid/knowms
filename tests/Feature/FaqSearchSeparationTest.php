<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\FaqVersion;
use App\Models\Tag;
use App\Services\FaqSimilarityService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FaqSearchSeparationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->createMinimalSqliteSchema();
        $this->seedTestFaqs();
    }

    private function createMinimalSqliteSchema(): void
    {
        Schema::dropIfExists('tdetail_faqs');
        Schema::dropIfExists('tfaqsversion');
        Schema::dropIfExists('tdaftarpustaka');
        Schema::dropIfExists('ttag');
        Schema::dropIfExists('tfaqs');
        Schema::dropIfExists('tusers');
        Schema::dropIfExists('tdirektorat');

        Schema::create('tdirektorat', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama', 100);
            $table->boolean('is_pusat')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tusers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama', 100);
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->integer('tRoles_id')->default(2);
            $table->integer('tDirektorat_id')->nullable();
            $table->timestamps();
        });

        Schema::create('tfaqs', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->integer('id_dataentry')->default(1);
            $table->integer('tDirektorat_id')->nullable();
            $table->integer('id_riwayat_import')->nullable();
            $table->text('pertanyaan');
            $table->timestamps();
        });

        Schema::create('ttag', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama', 100);
            $table->timestamps();
        });

        Schema::create('tdetail_faqs', function (Blueprint $table) {
            $table->string('tFaqs_id', 20);
            $table->integer('tTag_id');
        });

        Schema::create('tdaftarpustaka', function (Blueprint $table) {
            $table->increments('id');
            $table->string('link')->nullable();
            $table->string('upload')->nullable();
            $table->string('jenis')->default('Public');
            $table->timestamps();
        });

        Schema::create('tfaqsversion', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->integer('tDaftarPustakas_id')->nullable();
            $table->string('tFaqs_id', 20);
            $table->unsignedSmallInteger('version_number')->default(1);
            $table->integer('id_validator')->nullable();
            $table->text('jawaban');
            $table->text('jawaban_sebelumnya')->nullable();
            $table->string('status', 40)->default('Draf');
            $table->string('rejected_by_stage', 20)->nullable();
            $table->boolean('is_latest')->default(true);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('tfaq_alternatif', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tFaqs_id', 20);
            $table->string('pertanyaan_alternatif', 500);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    private function seedTestFaqs(): void
    {
        DB::table('tdirektorat')->insert([
            ['id' => 1, 'nama' => 'Direktorat Akademik', 'is_pusat' => 1, 'is_active' => 1],
            ['id' => 2, 'nama' => 'Fakultas Teknik', 'is_pusat' => 0, 'is_active' => 1],
        ]);

        // 1. FAQ Terbit dengan V1 (Terbit) dan V2 (Draf yang belum disetujui) + Tag + Alternatif Pertanyaan
        Faq::create([
            'id'             => 'FAQ-0001',
            'id_dataentry'   => 1,
            'tDirektorat_id' => 2,
            'pertanyaan'     => 'Bagaimana syarat pendaftaran Yudisium dan batas skor minimal TOEFL di Fakultas Teknik?',
        ]);
        FaqVersion::create([
            'id'             => 'FAQ-0001-V1',
            'tFaqs_id'       => 'FAQ-0001',
            'version_number' => 1,
            'jawaban'        => 'Versi Resmi Terbit: Skor TOEFL/EPT minimal 475.',
            'status'         => 'Terbit',
            'is_latest'      => false,
        ]);
        FaqVersion::create([
            'id'             => 'FAQ-0001-V2',
            'tFaqs_id'       => 'FAQ-0001',
            'version_number' => 2,
            'jawaban'        => 'RAHASIA DRAF V2 BELUM VALIDASI: Skor TOEFL rencananya naik menjadi 525.',
            'status'         => 'Draf',
            'is_latest'      => true,
        ]);

        $tagToefl = Tag::create(['nama' => 'Sertifikasi Bahasa Inggris']);
        DB::table('tdetail_faqs')->insert(['tFaqs_id' => 'FAQ-0001', 'tTag_id' => $tagToefl->id]);

        // 2. FAQ Draf murni (belum pernah Terbit)
        Faq::create([
            'id'             => 'FAQ-0002',
            'id_dataentry'   => 1,
            'tDirektorat_id' => 1,
            'pertanyaan'     => 'Bagaimana prosedur pengajuan dispensasi lomba robotika nasional?',
        ]);
        FaqVersion::create([
            'id'             => 'FAQ-0002-V1',
            'tFaqs_id'       => 'FAQ-0002',
            'version_number' => 1,
            'jawaban'        => 'Ajukan H-3 sebelum keberangkatan.',
            'status'         => 'Draf',
            'is_latest'      => true,
        ]);

        // 3. FAQ Menunggu Validasi Admik
        Faq::create([
            'id'             => 'FAQ-0003',
            'id_dataentry'   => 1,
            'tDirektorat_id' => 1,
            'pertanyaan'     => 'Apa syarat pendaftaran wisuda periode gasal dan pengisian tracer study?',
        ]);
        FaqVersion::create([
            'id'             => 'FAQ-0003-V1',
            'tFaqs_id'       => 'FAQ-0003',
            'version_number' => 1,
            'jawaban'        => 'Unggah pasfoto dan isi tracer study.',
            'status'         => 'Menunggu Validasi Admik',
            'is_latest'      => true,
        ]);

        // 4. FAQ Ditolak
        Faq::create([
            'id'             => 'FAQ-0004',
            'id_dataentry'   => 1,
            'tDirektorat_id' => 1,
            'pertanyaan'     => 'Bagaimana cara meminjam laptop inventaris kampus untuk kuliah daring?',
        ]);
        FaqVersion::create([
            'id'             => 'FAQ-0004-V1',
            'tFaqs_id'       => 'FAQ-0004',
            'version_number' => 1,
            'jawaban'        => 'Ditolak validator.',
            'status'         => 'Ditolak',
            'is_latest'      => true,
        ]);
    }

    public function test_draft_pending_and_rejected_faqs_never_leak_to_public_search(): void
    {
        $service = new FaqSimilarityService();
        $publishedIds = $service->getPublishedCandidateFaqs()->pluck('id')->all();

        $this->assertSame(['FAQ-0001'], $publishedIds);

        // Query terhadap FAQ Draf, Menunggu Validasi, dan Ditolak wajib abstain di pencarian publik
        $draftSearch = $service->searchPublishedFaqs('dispensasi lomba robotika nasional');
        $this->assertTrue($draftSearch['abstained']);
        $this->assertEmpty($draftSearch['results']);

        $pendingSearch = $service->searchPublishedFaqs('pendaftaran wisuda periode gasal tracer study');
        $this->assertTrue($pendingSearch['abstained']);
        $this->assertEmpty($pendingSearch['results']);

        $rejectedSearch = $service->searchPublishedFaqs('meminjam laptop inventaris kampus');
        $this->assertTrue($rejectedSearch['abstained']);
        $this->assertEmpty($rejectedSearch['results']);
    }

    public function test_public_search_only_exposes_published_version_answer_not_newer_draft_version(): void
    {
        $service = new FaqSimilarityService();
        $res = $service->searchPublishedFaqs('Berapa skor minimal toefl');

        $this->assertFalse($res['abstained']);
        $this->assertSame('FAQ-0001', $res['results'][0]['id']);
        $this->assertSame('FAQ-0001-V1', $res['results'][0]['version_id']);
        $this->assertStringContainsString('475', $res['results'][0]['jawaban']);
        $this->assertStringNotContainsString('RAHASIA DRAF V2', $res['results'][0]['jawaban']);
    }

    public function test_public_search_leverages_approved_tags_without_mutating_title(): void
    {
        $service = new FaqSimilarityService();
        $res = $service->searchPublishedFaqs('sertifikasi bahasa inggris teknik');

        $this->assertFalse($res['abstained']);
        $this->assertSame('FAQ-0001', $res['results'][0]['id']);
        // Pertanyaan resmi tetap utuh, tidak tercemar oleh teks alternatif/tag
        $this->assertSame(
            'Bagaimana syarat pendaftaran Yudisium dan batas skor minimal TOEFL di Fakultas Teknik?',
            $res['results'][0]['pertanyaan']
        );
    }

    public function test_internal_duplicate_detection_still_finds_draft_pending_and_rejected_faqs(): void
    {
        $service = new FaqSimilarityService();

        $internalDraft = $service->analyzeQuestion('Bagaimana prosedur pengajuan dispensasi lomba robotika nasional?');
        $this->assertTrue($internalDraft['has_exact_match']);
        $this->assertSame('FAQ-0002', $internalDraft['exact_match']['id']);

        $exactDraft = $service->findExactDraftMatch(
            'Bagaimana prosedur pengajuan dispensasi lomba robotika nasional?',
            'Ajukan H-3 sebelum keberangkatan.'
        );
        $this->assertNotNull($exactDraft);
        $this->assertSame('FAQ-0002', $exactDraft['id']);
        $this->assertSame('FAQ-0002-V1', $exactDraft['version_id']);
    }

    public function test_public_search_http_endpoint_returns_only_published_results(): void
    {
        $okResponse = $this->getJson(route('faq.search', ['q' => 'berapa skor minimal toefl']));
        $okResponse->assertOk()
            ->assertJsonPath('abstained', false)
            ->assertJsonPath('results.0.id', 'FAQ-0001')
            ->assertJsonPath('results.0.version_id', 'FAQ-0001-V1');

        $noAnsResponse = $this->getJson(route('faq.search', ['q' => 'dispensasi lomba robotika nasional']));
        $noAnsResponse->assertOk()
            ->assertJsonPath('abstained', true)
            ->assertJsonCount(0, 'results');
    }
}
