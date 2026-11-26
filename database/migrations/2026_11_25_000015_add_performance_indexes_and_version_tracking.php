<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function hasIndex(string $table, string $indexName): bool
    {
        if (DB::getDriverName() !== 'mysql' && DB::getDriverName() !== 'mariadb') {
            return false;
        }
        $rows = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return !empty($rows);
    }

    private function addIndexIfMissing(string $table, string $indexName, array $columns, bool $fulltext = false): void
    {
        if (!Schema::hasTable($table) || $this->hasIndex($table, $indexName)) {
            return;
        }

        if ($fulltext) {
            if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
                $colsSql = implode(', ', array_map(fn ($c) => "`{$c}`", $columns));
                DB::statement("ALTER TABLE `{$table}` ADD FULLTEXT INDEX `{$indexName}` ({$colsSql})");
            }
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
            $t->index($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table) || !$this->hasIndex($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($indexName) {
            $t->dropIndex($indexName);
        });
    }

    public function up(): void
    {
        // 1. Tambah kolom terindeks version_number & is_latest pada tfaqsversion
        //    agar filter versi terbaru & sorting tidak lagi memakai SUBSTRING_INDEX() non-sargable.
        if (Schema::hasTable('tfaqsversion')) {
            Schema::table('tfaqsversion', function (Blueprint $table) {
                if (!Schema::hasColumn('tfaqsversion', 'version_number')) {
                    $table->unsignedSmallInteger('version_number')->default(1)->after('tFaqs_id');
                }
                if (!Schema::hasColumn('tfaqsversion', 'is_latest')) {
                    $table->boolean('is_latest')->default(false)->after('status');
                }
            });

            if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
                // Backfill version_number dari akhiran -Vn pada id
                DB::statement("
                    UPDATE tfaqsversion
                    SET version_number = CASE
                        WHEN id LIKE '%-V%' AND CAST(SUBSTRING_INDEX(id, '-V', -1) AS UNSIGNED) > 0
                            THEN CAST(SUBSTRING_INDEX(id, '-V', -1) AS UNSIGNED)
                        ELSE 1
                    END
                ");

                // Normalisasi satu kali versi lama yang masih berstatus Draf/Menunggu Validasi/Ditolak -> Terbit
                DB::statement("
                    UPDATE tfaqsversion fv
                    INNER JOIN (
                        SELECT tFaqs_id, MAX(version_number) AS max_v
                        FROM tfaqsversion
                        GROUP BY tFaqs_id
                    ) lm ON lm.tFaqs_id = fv.tFaqs_id
                    SET fv.status = 'Terbit'
                    WHERE fv.version_number < lm.max_v
                      AND fv.status IN ('Draf', 'Menunggu Validasi', 'Ditolak')
                ");

                // Backfill flag is_latest = 1 hanya pada versi tertinggi tiap FAQ
                DB::statement("UPDATE tfaqsversion SET is_latest = 0");
                DB::statement("
                    UPDATE tfaqsversion fv
                    INNER JOIN (
                        SELECT tFaqs_id, MAX(version_number) AS max_v
                        FROM tfaqsversion
                        GROUP BY tFaqs_id
                    ) lm ON lm.tFaqs_id = fv.tFaqs_id AND lm.max_v = fv.version_number
                    SET fv.is_latest = 1
                ");
            }

            $this->addIndexIfMissing('tfaqsversion', 'idx_tfaqsversion_faq_ver', ['tFaqs_id', 'version_number']);
            $this->addIndexIfMissing('tfaqsversion', 'idx_tfaqsversion_latest_status', ['is_latest', 'status', 'tFaqs_id']);
            $this->addIndexIfMissing('tfaqsversion', 'idx_tfaqsversion_faq_latest_status', ['tFaqs_id', 'is_latest', 'status']);
            $this->addIndexIfMissing('tfaqsversion', 'idx_tfaqsversion_status_updated', ['status', 'updated_at']);
            $this->addIndexIfMissing('tfaqsversion', 'idx_tfaqsversion_updated_at', ['updated_at']);
        }

        // 2. Index sorting & FULLTEXT pencarian pada tfaqs
        if (Schema::hasTable('tfaqs')) {
            $this->addIndexIfMissing('tfaqs', 'idx_tfaqs_updated_at', ['updated_at']);
            $this->addIndexIfMissing('tfaqs', 'idx_tfaqs_created_at', ['created_at']);
            $this->addIndexIfMissing('tfaqs', 'ft_tfaqs_pertanyaan', ['pertanyaan'], true);
        }

        // 3. Index rekomendasi, interaksi & cek duplikat view pada tlogmahasiswa
        if (Schema::hasTable('tlogmahasiswa')) {
            $this->addIndexIfMissing('tlogmahasiswa', 'idx_tlog_waktu_faq_jenis', ['waktu_pencarian', 'tFaqs_id', 'jenis_interaksi']);
            $this->addIndexIfMissing('tlogmahasiswa', 'idx_tlog_faq_jenis_hasil', ['tFaqs_id', 'jenis_interaksi', 'hasil']);
            $this->addIndexIfMissing('tlogmahasiswa', 'idx_tlog_session_faq_jenis', ['tSession_id', 'tFaqs_id', 'jenis_interaksi']);
        }

        // 4. Index pencarian & pengurutan tag pada ttag
        $this->addIndexIfMissing('ttag', 'idx_ttag_nama', ['nama']);

        // 5. Index rentang tanggal tren sesi pada tsession
        $this->addIndexIfMissing('tsession', 'idx_tsession_created_at', ['created_at']);

        // 6. Index polling panggilan aktif pada tvideocall
        $this->addIndexIfMissing('tvideocall', 'idx_tvideocall_status_created', ['status', 'created_at']);

        // 7. Index lookup sumber pustaka pada tdaftarpustaka
        $this->addIndexIfMissing('tdaftarpustaka', 'idx_tdaftarpustaka_judul', ['judul']);
        $this->addIndexIfMissing('tdaftarpustaka', 'idx_tdaftarpustaka_link', ['link']);
    }

    public function down(): void
    {
        $this->dropIndexIfExists('tdaftarpustaka', 'idx_tdaftarpustaka_link');
        $this->dropIndexIfExists('tdaftarpustaka', 'idx_tdaftarpustaka_judul');
        $this->dropIndexIfExists('tvideocall', 'idx_tvideocall_status_created');
        $this->dropIndexIfExists('tsession', 'idx_tsession_created_at');
        $this->dropIndexIfExists('ttag', 'idx_ttag_nama');
        $this->dropIndexIfExists('tlogmahasiswa', 'idx_tlog_session_faq_jenis');
        $this->dropIndexIfExists('tlogmahasiswa', 'idx_tlog_faq_jenis_hasil');
        $this->dropIndexIfExists('tlogmahasiswa', 'idx_tlog_waktu_faq_jenis');
        $this->dropIndexIfExists('tfaqs', 'ft_tfaqs_pertanyaan');
        $this->dropIndexIfExists('tfaqs', 'idx_tfaqs_created_at');
        $this->dropIndexIfExists('tfaqs', 'idx_tfaqs_updated_at');
        $this->dropIndexIfExists('tfaqsversion', 'idx_tfaqsversion_updated_at');
        $this->dropIndexIfExists('tfaqsversion', 'idx_tfaqsversion_status_updated');
        $this->dropIndexIfExists('tfaqsversion', 'idx_tfaqsversion_faq_latest_status');
        $this->dropIndexIfExists('tfaqsversion', 'idx_tfaqsversion_latest_status');
        $this->dropIndexIfExists('tfaqsversion', 'idx_tfaqsversion_faq_ver');

        if (Schema::hasTable('tfaqsversion')) {
            Schema::table('tfaqsversion', function (Blueprint $table) {
                if (Schema::hasColumn('tfaqsversion', 'is_latest')) {
                    $table->dropColumn('is_latest');
                }
                if (Schema::hasColumn('tfaqsversion', 'version_number')) {
                    $table->dropColumn('version_number');
                }
            });
        }
    }
};
