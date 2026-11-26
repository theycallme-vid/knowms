<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class LogMahasiswa extends Model
{
    protected $table = 'tlogmahasiswa';
    public $timestamps = false;

    protected $fillable = [
        'jenis_interaksi',
        'keyword',
        'hasil',
        'waktu_pencarian',
        'tSession_id',
        'tFaqs_id',
    ];

    protected $casts = [
        'waktu_pencarian' => 'datetime',
        'hasil'           => 'integer',
    ];

    /** Sesi pencarian */
    public function session()
    {
        return $this->belongsTo(Session::class, 'tSession_id');
    }

    /** FAQ yang ditemukan */
    public function faq()
    {
        return $this->belongsTo(Faq::class, 'tFaqs_id');
    }

    /**
     * Mengambil ringkasan jumlah Views, Likes (Membantu), dan Dislikes (Belum Membantu)
     * untuk setiap FAQ memakai covering index idx_tlog_faq_jenis_hasil (tanpa GROUP BY LONGTEXT keyword).
     *
     * @param  string[]|null  $faqIds
     * @return array<string, array{views:int, likes:int, dislikes:int}>
     */
    public static function getFaqInteractionSummary(?array $faqIds = null): array
    {
        $compute = function (?array $ids): array {
            $summary = [];

            try {
                $query = DB::table('tlogmahasiswa')
                    ->whereNotNull('tFaqs_id')
                    ->select(
                        'tFaqs_id as faq_id',
                        'jenis_interaksi',
                        'hasil',
                        DB::raw('COUNT(*) as total')
                    )
                    ->groupBy('tFaqs_id', 'jenis_interaksi', 'hasil');

                if (!empty($ids)) {
                    $query->whereIn('tFaqs_id', $ids);
                }

                foreach ($query->get() as $row) {
                    $fid = (string) $row->faq_id;
                    if (!isset($summary[$fid])) {
                        $summary[$fid] = ['views' => 0, 'likes' => 0, 'dislikes' => 0];
                    }

                    $type = strtolower((string) ($row->jenis_interaksi ?? ''));
                    $cnt  = (int) $row->total;

                    if ($type === 'like') {
                        $summary[$fid]['likes'] += $cnt;
                    } elseif ($type === 'dislike' || ($type === '' && (int) $row->hasil === 0)) {
                        $summary[$fid]['dislikes'] += $cnt;
                    } else {
                        $summary[$fid]['views'] += $cnt;
                    }
                }
            } catch (Throwable $e) {
                // Kembalikan array kosong bila tabel belum siap
            }

            return $summary;
        };

        if (empty($faqIds)) {
            try {
                return Cache::store('file')->remember('kms_faq_interaction_summary_all', 30, fn () => $compute(null));
            } catch (Throwable $e) {
                return $compute(null);
            }
        }

        return $compute($faqIds);
    }
}
