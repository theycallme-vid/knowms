<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Penyesuaian tabel tdirektorat (is_pusat, is_active, dan perluas panjang nama unit)
        if (Schema::hasTable('tdirektorat')) {
            DB::statement('ALTER TABLE tdirektorat MODIFY nama VARCHAR(100) NOT NULL');

            Schema::table('tdirektorat', function (Blueprint $table) {
                if (!Schema::hasColumn('tdirektorat', 'is_pusat')) {
                    $table->boolean('is_pusat')->default(0)->after('nama');
                }
                if (!Schema::hasColumn('tdirektorat', 'is_active')) {
                    $table->boolean('is_active')->default(1)->after('is_pusat');
                }
            });

            DB::table('tdirektorat')
                ->where('id', 1)
                ->orWhereRaw('LOWER(nama) = ?', ['direktorat akademik'])
                ->update(['is_pusat' => 1, 'is_active' => 1]);
        }

        // 2. Penyesuaian tabel tfaqs (ikat kepemilikan FAQ ke unit asal + index komposit)
        if (Schema::hasTable('tfaqs')) {
            Schema::table('tfaqs', function (Blueprint $table) {
                if (!Schema::hasColumn('tfaqs', 'tDirektorat_id')) {
                    $table->integer('tDirektorat_id')->nullable()->after('id_dataentry');
                    $table->foreign('tDirektorat_id', 'fk_tFaqs_tDirektorat1')
                        ->references('id')
                        ->on('tdirektorat')
                        ->nullOnDelete();
                }
            });

            $hasIndex = collect(DB::select("SHOW INDEX FROM tfaqs WHERE Key_name = 'idx_tfaqs_dir_updated'"))->isNotEmpty();
            if (!$hasIndex) {
                Schema::table('tfaqs', function (Blueprint $table) {
                    $table->index(['tDirektorat_id', 'updated_at'], 'idx_tfaqs_dir_updated');
                });
            }

            // Backfill unit asal FAQ dari unit staf pembuat (default ke Direktorat Akademik id=1)
            $defaultDirId = DB::table('tdirektorat')->where('is_pusat', 1)->value('id')
                ?? DB::table('tdirektorat')->orderBy('id')->value('id');

            if ($defaultDirId) {
                DB::statement(
                    'UPDATE tfaqs f
                     LEFT JOIN tusers u ON u.id = f.id_dataentry
                     SET f.tDirektorat_id = COALESCE(u.tDirektorat_id, ?)
                     WHERE f.tDirektorat_id IS NULL',
                    [(int) $defaultDirId]
                );
            }
        }

        // 3. Penyesuaian tabel tfaqsversion (6 status validasi berjenjang + penanda sumber penolakan)
        if (Schema::hasTable('tfaqsversion')) {
            DB::statement("ALTER TABLE tfaqsversion MODIFY status VARCHAR(40) NOT NULL DEFAULT 'Draf'");
            DB::statement("UPDATE tfaqsversion SET status = 'Draf' WHERE LOWER(status) IN ('draft', 'draf')");
            DB::statement("UPDATE tfaqsversion SET status = 'Menunggu Validasi Unit' WHERE LOWER(status) = 'menunggu validasi unit'");
            DB::statement("UPDATE tfaqsversion SET status = 'Menunggu Validasi Admik' WHERE LOWER(status) IN ('submitted', 'pending', 'menunggu validasi', 'menunggu validasi admik')");
            DB::statement("UPDATE tfaqsversion SET status = 'Diterima' WHERE LOWER(status) IN ('approved', 'accepted', 'disetujui', 'diterima')");
            DB::statement("UPDATE tfaqsversion SET status = 'Ditolak' WHERE LOWER(status) IN ('rejected', 'ditolak')");
            DB::statement("UPDATE tfaqsversion SET status = 'Terbit' WHERE LOWER(status) IN ('active', 'inactive', 'terbit', 'publish')");
            DB::statement("UPDATE tfaqsversion SET status = 'Draf' WHERE status NOT IN ('Draf', 'Menunggu Validasi Unit', 'Menunggu Validasi Admik', 'Diterima', 'Ditolak', 'Terbit')");
            DB::statement("ALTER TABLE tfaqsversion MODIFY status ENUM('Draf', 'Menunggu Validasi Unit', 'Menunggu Validasi Admik', 'Diterima', 'Ditolak', 'Terbit') NOT NULL DEFAULT 'Draf'");

            if (!Schema::hasColumn('tfaqsversion', 'rejected_by_stage')) {
                Schema::table('tfaqsversion', function (Blueprint $table) {
                    $table->enum('rejected_by_stage', ['unit', 'admik'])->nullable()->after('status');
                });

                DB::table('tfaqsversion')
                    ->where('status', 'Ditolak')
                    ->whereNull('rejected_by_stage')
                    ->update(['rejected_by_stage' => 'admik']);
            }
        }

        // 4. Tabel baru tfaq_validasi_log untuk riwayat milestone & catatan validasi per tahap
        if (!Schema::hasTable('tfaq_validasi_log')) {
            Schema::create('tfaq_validasi_log', function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->string('tFaqs_id', 20);
                $table->foreign('tFaqs_id', 'fk_tFaqValidasiLog_tFaqs1')
                    ->references('id')
                    ->on('tfaqs')
                    ->cascadeOnDelete();

                $table->string('tFaqsVersion_id', 15);
                $table->foreign('tFaqsVersion_id', 'fk_tFaqValidasiLog_tFaqsVersion1')
                    ->references('id')
                    ->on('tfaqsversion')
                    ->cascadeOnDelete();

                $table->enum('tahap', ['pengajuan', 'unit', 'admik', 'publikasi']);
                $table->enum('aksi', ['ajukan', 'setujui', 'tolak', 'revisi', 'terbit', 'jadwalkan']);

                $table->integer('id_actor')->nullable();
                $table->foreign('id_actor', 'fk_tFaqValidasiLog_tUsers1')
                    ->references('id')
                    ->on('tusers')
                    ->nullOnDelete();

                $table->text('catatan')->nullable();
                $table->timestamp('created_at')->nullable()->useCurrent();

                $table->index(['tFaqs_id', 'created_at'], 'idx_validasi_log_faq_created');
                $table->index(['tFaqsVersion_id', 'created_at'], 'idx_validasi_log_ver_created');
            });
        }

        try {
            Cache::store('file')->forget('kms_schema_faqversion_v2');
            Cache::store('file')->forever('kms_schema_faqversion_v3', true);
        } catch (\Throwable $e) {
            // Abaikan bila file cache tidak dapat diakses
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tfaq_validasi_log');

        if (Schema::hasTable('tfaqsversion')) {
            if (Schema::hasColumn('tfaqsversion', 'rejected_by_stage')) {
                Schema::table('tfaqsversion', function (Blueprint $table) {
                    $table->dropColumn('rejected_by_stage');
                });
            }

            DB::statement("ALTER TABLE tfaqsversion MODIFY status VARCHAR(40) NOT NULL DEFAULT 'Draf'");
            DB::statement("UPDATE tfaqsversion SET status = 'Menunggu Validasi' WHERE status IN ('Menunggu Validasi Unit', 'Menunggu Validasi Admik')");
            DB::statement("ALTER TABLE tfaqsversion MODIFY status ENUM('Draf', 'Menunggu Validasi', 'Diterima', 'Ditolak', 'Terbit') NOT NULL DEFAULT 'Draf'");
        }

        if (Schema::hasTable('tfaqs')) {
            $hasIndex = collect(DB::select("SHOW INDEX FROM tfaqs WHERE Key_name = 'idx_tfaqs_dir_updated'"))->isNotEmpty();
            if ($hasIndex) {
                Schema::table('tfaqs', function (Blueprint $table) {
                    $table->dropIndex('idx_tfaqs_dir_updated');
                });
            }

            if (Schema::hasColumn('tfaqs', 'tDirektorat_id')) {
                Schema::table('tfaqs', function (Blueprint $table) {
                    $table->dropForeign('fk_tFaqs_tDirektorat1');
                    $table->dropColumn('tDirektorat_id');
                });
            }
        }

        if (Schema::hasTable('tdirektorat')) {
            Schema::table('tdirektorat', function (Blueprint $table) {
                if (Schema::hasColumn('tdirektorat', 'is_active')) {
                    $table->dropColumn('is_active');
                }
                if (Schema::hasColumn('tdirektorat', 'is_pusat')) {
                    $table->dropColumn('is_pusat');
                }
            });
        }

        try {
            Cache::store('file')->forget('kms_schema_faqversion_v3');
        } catch (\Throwable $e) {
            // Abaikan
        }
    }
};
