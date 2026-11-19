# Catatan Audit Kode KnowMS

Tanggal audit: 2026-11-19  
Ringkasan: KnowMS adalah sistem Knowledge Management berbasis Laravel 12 untuk pengelolaan FAQ universitas (mahasiswa/tamu, Data Entry, Validator, dan Super Admin).

> **Dokumentasi Algoritma Rekomendasi FAQ, Views/Like/Dislike & Pengaturan Sistem:**  
> Lihat penjelasan lengkap rumus, alur pencatatan, dan daftar setting di [algoritma-rekomendasi.md](algoritma-rekomendasi.md).

---

## Must Fix (Wajib Diperbaiki)

1. **Route publik menjalankan database seeder** (`routes/web.php:L21-L52`)
   - **Konteks:** Route `/faq-seeder-ready` terbuka tanpa login atau cek role.
   - **Masalah:** Siapa pun dapat mengakses URL ini dan memicu `Artisan::call('db:seed')` untuk menimpa/mengisi ulang data FAQ di database.
   - **Solusi:** Hapus route ini dari `routes/web.php` atau batasi hanya di environment `local` di balik `auth` dan `role:Super Admin`.
   - **Dampak jika dilewati:** Database dapat di-reseed oleh siapa saja dari luar.

2. **Dashboard Super Admin error karena kolom tidak ada** (`app/Http/Controllers/superadmin/PenggunaAksesController.php:L84`)
   - **Konteks:** Dashboard menghitung tren sesi 7 hari terakhir dari tabel `tsession`.
   - **Masalah:** Query memanggil `Session::whereDate('waktu_mulai', ...)`, padahal tabel `tsession` tidak punya kolom `waktu_mulai` (kolom waktunya adalah `created_at`). Error SQL tertangkap `try-catch` sehingga seluruh metrik dashboard selalu jatuh ke angka fallback/dummy.
   - **Solusi:** Ganti `'waktu_mulai'` menjadi `'created_at'`.
   - **Dampak jika dilewati:** Statistik dashboard Super Admin selalu menampilkan data palsu.

3. **Validator bisa mengelola user dan menaikkan hak akses sendiri** (`routes/web.php:L104-L112`)
   - **Konteks:** Route manajemen user (`/superadmin/pengguna-akses/*`) memakai middleware `role:Super Admin,Validator`.
   - **Masalah:** Akun dengan role `Validator` dapat membuat user baru, mengubah role dirinya sendiri atau orang lain menjadi `Super Admin`, atau menonaktifkan akun admin.
   - **Solusi:** Ubah middleware grup route tersebut menjadi `role:Super Admin` saja.
   - **Dampak jika dilewati:** Celah privilege escalation; Validator bisa mengambil alih sistem.

4. **File target hilang membuat `/simulator` error 500** (`routes/web.php:L16-L18`)
   - **Konteks:** Route `/simulator` mengembalikan `response()->file(base_path('index.php'))`.
   - **Masalah:** File `index.php` tidak ada di root project (entry point Laravel ada di `public/index.php`), sehingga route ini langsung throw `FileNotFoundException`.
   - **Solusi:** Hapus route `/simulator` atau arahkan ke file/view yang valid.
   - **Dampak jika dilewati:** Mengakses `/simulator` selalu menghasilkan server error 500.

5. **Edit profil user berisiko menonaktifkan akun secara tidak sengaja** (`app/Http/Controllers/superadmin/PenggunaAksesController.php:L273`)
   - **Konteks:** Method `PenggunaAksesController::update` memperbarui data user.
   - **Masalah:** Status aktif diisi dengan `$request->has('is_active') ? (bool)$request->is_active : false`. Pada form HTML, jika checkbox tidak dicentang atau tidak dikirim, `is_active` otomatis berubah menjadi `false`.
   - **Solusi:** Update `is_active` hanya jika field-nya dikirim atau pastikan state checkbox ditangani eksplisit.
   - **Dampak jika dilewati:** Staf yang masih aktif bisa otomatis terkunci hanya karena namanya diedit.

6. **Race condition saat generate ID FAQ secara bersamaan** (`app/Models/Faq.php:L64-L104`)
   - **Konteks:** `Faq::generateNextId()` membuat ID berurutan seperti `FAQ-0005`.
   - **Masalah:** Menggunakan pola check-then-write (`while (exists)`) tanpa database lock. Dua proses/user yang membuat FAQ bersamaan dapat memperoleh ID yang sama dan salah satunya gagal karena bentrok primary key.
   - **Solusi:** Gunakan sequence atomik atau transaksi dengan `lockForUpdate()`.
   - **Dampak jika dilewati:** Input atau import bersamaan akan gagal dengan error duplikat primary key.

7. **Tidak ada rate limiting pada endpoint login** (`routes/web.php:L73`, `app/Http/Controllers/AuthController.php:L25-L62`)
   - **Konteks:** Endpoint `POST /login` memvalidasi email dan password.
   - **Masalah:** Tidak ada middleware `throttle` maupun `RateLimiter` di controller.
   - **Solusi:** Tambahkan middleware `throttle:5,1` pada route `POST /login`.
   - **Dampak jika dilewati:** Akun rentan terhadap serangan brute-force password.

8. **Data dummy hardcoded bisa tersimpan ke database dari halaman pratinjau** (`app/Http/Controllers/dataentry/FaqController.php:L375-L456`)
   - **Konteks:** Method `pratinjau` menampilkan daftar FAQ hasil unggahan sebelum disimpan.
   - **Masalah:** Jika session kosong saat membuka `/data-entry/pratinjau-unggah`, controller menyisipkan 5 FAQ dummy hardcoded ke session `'uploaded_faqs'`. Jika user menekan simpan, data dummy tersebut masuk ke database.
   - **Solusi:** Redirect kembali ke `/data-entry/buat-faq` dengan pesan error jika session kosong.
   - **Dampak jika dilewati:** Data contoh/dummy berisiko ikut tersimpan dan terbit sebagai FAQ resmi.

---

## Should Fix (Sebaiknya Diperbaiki)

9. **Query DDL skema tabel dijalankan di setiap HTTP request** (`app/Models/DaftarPustaka.php:L55-L85`)
   - **Konteks:** `DaftarPustaka::ensurePublicPrivateEnum()` menjalankan `SHOW COLUMNS` dan `ALTER TABLE` secara runtime.
   - **Masalah:** Dipanggil di 16 tempat pada controller web. Ini memicu metadata lock di MySQL saat traffic ramai dan gagal di database non-MySQL (seperti SQLite saat testing), padahal migration sudah mengatur skemanya.
   - **Solusi:** Hapus method `ensurePublicPrivateEnum()` beserta seluruh 16 pemanggilannya.
   - **Dampak jika dilewati:** Request melambat/macet saat beban bersamaan dan test SQLite bermasalah.

10. **Pengecekan duplikat memuat seluruh database berulang kali saat import** (`app/Services/FaqSimilarityService.php:L186-L221`, `app/Services/FileImportParser.php:L478`)
    - **Konteks:** `mapRowsToFaqPreview()` mengecek kemiripan setiap baris Excel ke database.
    - **Masalah:** Untuk setiap baris, `analyzeQuestion()` memanggil `getCandidateFaqs()` yang menarik seluruh FAQ & versi dari DB sebanyak 2 kali. File berisi 100 baris memicu 200 full-table query.
    - **Solusi:** Ambil daftar kandidat FAQ dari database satu kali sebelum loop, lalu oper koleksinya ke `analyzeQuestion()`.
    - **Dampak jika dilewati:** Import file 50–100 baris rawan kehabisan memori atau timeout 30 detik.

11. **Job publikasi terjadwal dijalankan sinkron di dalam request web** (`app/Models/FaqVersion.php:L129-L168`)
    - **Konteks:** `processDueScheduledPublications()` mengaktifkan FAQ yang jadwal terbitnya sudah lewat.
    - **Masalah:** Dipanggil di 11 method controller (termasuk halaman publik `/faq` dan `/guest`), padahal sudah terjadwal otomatis setiap menit di `routes/console.php`.
    - **Solusi:** Hapus seluruh pemanggilan di controller web dan biarkan `routes/console.php` yang menjalankannya.
    - **Dampak jika dilewati:** Halaman publik menanggung beban query tulis dan potensi race condition.

12. **File dokumen ukuran besar disimpan sebagai BLOB di MySQL** (`app/Http/Controllers/dataentry/FaqController.php:L1439-L1490`)
    - **Konteks:** Dokumen Sumber Pustaka (maks. 10MB) disimpan langsung ke kolom `upload` (`MEDIUMBLOB`) di tabel `tdaftarpustaka`.
    - **Masalah:** `sumberPustakaIndex` memanggil `DaftarPustaka::get()` yang menarik seluruh kolom termasuk isi biner dokumen untuk semua baris ke memori PHP sekaligus.
    - **Solusi:** Simpan file ke disk (`Storage::disk('local')->putFile()`) dan simpan path-nya saja di database.
    - **Dampak jika dilewati:** Halaman daftar pustaka akan kehabisan memori PHP seiring bertambahnya dokumen.

13. **`FaqController` terlalu gemuk (mencampur 4 domain berbeda)** (`app/Http/Controllers/dataentry/FaqController.php`)
    - **Konteks:** Satu file controller sepanjang 1.649 baris menangani CRUD FAQ, Import Excel/PDF, Kata Kunci (`ttag`), dan Sumber Pustaka (`tdaftarpustaka`).
    - **Solusi:** Pecah menjadi controller terpisah: `FaqController`, `FaqImportController`, `TagController`, dan `SumberPustakaController`.
    - **Dampak jika dilewati:** Sulit dirawat dan perubahan kecil berisiko merusak fitur lain.

14. **Jadwal publikasi disimpan sebagai teks terformat di kolom `catatan`** (`app/Models/FaqVersion.php:L64-L124`)
    - **Konteks:** Jadwal disimpan dalam format `[PUBLISH_SCHEDULE|scheduled|...|...]` pada kolom teks `catatan`.
    - **Masalah:** Pencarian harus memakai `LIKE '[PUBLISH_SCHEDULE|%'` (tidak bisa diindeks) dan rawan rusak jika kolom catatan terisi teks lain.
    - **Solusi:** Tambah kolom `scheduled_at` (`timestamp` nullable) pada tabel `tfaqsversion` via migration.
    - **Dampak jika dilewati:** Query penjadwalan lambat dan parsing rentan error.

15. **Alur bisnis utama belum memiliki automated test** (`tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`)
    - **Konteks:** Folder `tests/` hanya berisi file bawaan Laravel.
    - **Solusi:** Tambah feature test untuk middleware role, transisi status FAQ (`Draft` -> `Submitted` -> `Approved` -> `Active`), dan deteksi duplikat.
    - **Dampak jika dilewati:** Bug regresi pada hak akses atau status FAQ baru ketahuan setelah dipakai user.

16. **Dead code dan file yatim (tidak terpakai)**
    - `app/Http/Controllers/superadmin/DashboardSupAdminController.php` (controller kosong)
    - `resources/views/superadmin/daftarFAQ.blade.php` & `resources/views/validator/dashboard.blade.php` (tidak pernah di-render)
    - `app/Models/Snapshot.php` & `app/Models/FormPertanyaanCs.php` (tidak dipakai controller/route mana pun)
    - `database/seeders/FaqSeederReady.php` (duplikat 48KB dari `FaqSeeder.php`)
    - **Solusi:** Hapus file-file yang tidak digunakan agar repo bersih.

---

## Nice to Have (Tambahan / Kebersihan Kode)

17. **Redirect berlapis pada route dashboard** (`routes/web.php:L139`, `app/Http/Controllers/validator/ValidatorFaqController.php:L23-L26`)
    - `/validator/dashboard` dan `/data-entry/dashboard` hanya me-redirect ke `daftarFAQ`. Sebaiknya langsung arahkan menu/route ke tujuan akhir.

18. **Fallback ID user hardcoded di controller** (`app/Http/Controllers/dataentry/FaqController.php:L618`, `app/Http/Controllers/validator/ValidatorFaqController.php:L231`)
    - Pola seperti `Auth::id() ?? User::where('tRoles_id', 2)->value('id') ?? 1` sebaiknya dihapus dan cukup gunakan `Auth::id()`.

19. **Konfigurasi `trustProxies(at: '*')` terlalu permisif** (`bootstrap/app.php:L16`)
    - Jika aplikasi tidak di belakang reverse proxy tepercaya, client bisa memalsukan header `X-Forwarded-For`. Sebaiknya batasi via `.env` (`TRUSTED_PROXIES`).

20. **Parser dan builder ZIP biner buatan sendiri** (`app/Services/FileImportParser.php:L672-L935`)
    - Sekitar 260 baris `pack()`/`unpack()` manual untuk membaca dan membuat ZIP/XLSX bisa disederhanakan dengan memakai ekstensi standar `ZipArchive` PHP 8.2+.
