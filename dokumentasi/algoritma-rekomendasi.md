# Dokumentasi Algoritma Rekomendasi FAQ, Pencatatan Views/Like/Dislike & Pengaturan Sistem

Terakhir diperbarui: 2026-11-19

Dokumen ini menjelaskan cara kerja pembacaan NRP (`1ABBCCDDD`), pencatatan interaksi mahasiswa ala YouTube (*Views*, *Like*, *Dislike*), rumus algoritma rekomendasi FAQ, serta cara mengubah bobot dan durasi *auto-logout* melalui menu **Pengaturan Sistem** di Super Admin.

---

## 1. Pembacaan Format NRP (`1ABBCCDDD`) & Estimasi Semester

Saat mahasiswa memasukkan NRP atau memindai QR KTM di halaman Portal (`PortalController::parseNrp`), 9 digit NRP dipecah menjadi:

| Posisi Digit | Kode | Arti | Contoh (`160423018`) |
| :--- | :--- | :--- | :--- |
| Digit 1–2 | `1A` | **Kode Fakultas** (`PortalController::FAKULTAS_MAP`) | `16` = Fakultas Teknik |
| Digit 3–4 | `BB` | **Kode Program Studi (Prodi)** | `04` |
| Digit 5–6 | `CC` | **Kode Angkatan** (`2000 + CC`) | `23` = Angkatan `2023` |
| Digit 7–9 | `DDD` | **Nomor Urut Mahasiswa** | `018` |

### Rumus Estimasi Semester (`PortalController::estimateSemester`)
Dari tahun angkatan (`20CC`), sistem menghitung semester aktif mahasiswa secara otomatis berdasarkan tahun dan bulan berjalan:
$$\text{Semester} = \max\left(1,\; (\text{Tahun Sekarang} - \text{Tahun Angkatan}) \times 2 + (\text{Bulan Sekarang} \ge 7 \text{ ? } 1 : 0)\right)$$
* Contoh pada November 2026:
  * Angkatan `23` (`2023`) $\rightarrow (2026 - 2023) \times 2 + 1 = \mathbf{7}$ (**Semester 7 / Tingkat Akhir**).
  * Angkatan `24` (`2024`) $\rightarrow (2026 - 2024) \times 2 + 1 = \mathbf{5}$ (**Semester 5 / Tingkat Menengah Atas**).
  * Angkatan `25` (`2025`) $\rightarrow (2026 - 2025) \times 2 + 1 = \mathbf{3}$ (**Semester 3 / Tingkat Menengah Awal**).
  * Angkatan `26` (`2026`) $\rightarrow (2026 - 2026) \times 2 + 1 = \mathbf{1}$ (**Semester 1 / Mahasiswa Baru**).

### Penyimpanan di Tabel `tsession`
Setiap sesi mahasiswa disimpan ke tabel `tsession` pada kolom `kode_fakultas` dengan format gabungan **`"kodeFakultas-kodeAngkatan"`** (contoh: **`"16-23"`**).
* Keuntungannya: sistem bisa langsung membedakan apakah sebuah FAQ diakses oleh mahasiswa dengan **fakultas & angkatan yang sama persis** (`16-23`), **fakultas yang sama** (`16%`), atau **angkatan yang sama lintas fakultas** (`%-23`).

---

## 2. Pencatatan Views, Like, dan Dislike (Ala YouTube)

Semua interaksi mahasiswa/guest pada halaman FAQ dicatat ke tabel **`tlogmahasiswa`** melalui endpoint `POST /faq/log-access` (`PortalController::logFaqAccess`) dengan kolom **`jenis_interaksi`**:

1. **`view` (Dilihat / Dibuka)**
   * **Kapan tercatat:** Saat mahasiswa mengklik pertanyaan FAQ untuk membuka accordion jawaban (`shown.bs.collapse`).
   * **Anti-Spam / Anti-Duplikat:** Dibatasi **maksimal 1x View per FAQ dalam 1 sesi kunjungan** (baik di sisi JavaScript menggunakan `Set` maupun pengecekan `tSession_id + tFaqs_id + jenis_interaksi = 'view'` di backend). Jadi meskipun mahasiswa membuka-tutup FAQ yang sama berkali-kali, jumlah *views* hanya bertambah 1.
2. **`like` (Membantu / "Ya, membantu")**
   * **Kapan tercatat:** Saat mahasiswa menekan tombol **"Ya, membantu"** di bawah jawaban FAQ (`hasil = 1`).
3. **`dislike` (Tidak Membantu / "Belum")**
   * **Kapan tercatat:** Saat mahasiswa menekan tombol **"Belum"** di bawah jawaban FAQ (`hasil = 0`).
   * **Anti-Spam Feedback:** Dalam 1 sesi, 1 mahasiswa hanya bisa memberikan 1 feedback per FAQ (jika awalnya klik *Like* lalu berubah ke *Dislike*, data feedback pada sesi tersebut diperbarui, bukan digandakan).

### Mekanisme Auto-Synchronize 30 Detik (Hanya Saat Ada Aktivitas / Tidak Idle)
* **Update UI Instan (*Optimistic UI*) & Antrean Lokal:** Saat mahasiswa/guest mengklik FAQ (*view*) atau menekan *Like/Dislike*, angka pada kartu FAQ di layar langsung bertambah seketika dan interaksi dimasukkan ke antrean lokal (`pendingSyncQueue`).
* **Auto-Sync Tiap 30 Detik (`Last synchronize HH:mm:ss`):** Setiap **30 detik**, sistem memeriksa apakah ada aktivitas di antrean lokal:
  * **Jika ada aktivitas:** Seluruh interaksi dalam 30 detik terakhir dikirim sekaligus dalam **1 request batch** ke `POST /faq/log-access`, memperbarui angka seluruh kartu FAQ dengan data terbaru dari server, dan memperbarui indikator **`Last synchronize HH:mm:ss`**.
  * **Jika komputer *idle* (tidak mengirim aktivitas apa pun):** Sistem **tidak** mengirim request ke server sama sekali (0 beban server).
  * **Anti-Hilang Saat Keluar / Auto-Logout:** Jika sesi berakhir (pop-up idle timeout / klik *Akhiri Sesi*) atau tab ditutup sebelum siklus 30 detik tiba, sisa antrean otomatis di-*flush* seketika menggunakan `navigator.sendBeacon` / `fetch keepalive`.

### Di Mana Statistik Views, Like, & Dislike Bisa Dilihat?
* **Halaman FAQ Mahasiswa & Guest:** Tampil langsung di setiap kartu FAQ (`👁 X dilihat · 👍 Y · 👎 Z`) dilengkapi indikator **`Last synchronize HH:mm:ss`** di bagian atas daftar FAQ.
* **Halaman Daftar FAQ & Modal Detail (Super Admin, Validator, Data Entry):** Tampil di baris tabel di samping tag kata kunci serta di dalam *pop-up modal View Detail*.
* **Dashboard Super Admin (`/superadmin/dashboard`):** Terdapat 2 tabel ringkasan khusus:
  1. **FAQ Paling Banyak Dilihat & Disukai** (Top 5 berdasarkan *views* dan *likes*).
  2. **FAQ Perlu Evaluasi (Dislike Tertinggi)** (Top 5 FAQ yang mendapat *dislike* agar jawabannya bisa direvisi).

---

## 3. Rumus Algoritma Rekomendasi FAQ (`PortalController::rankFaqsForStudent`)

Setiap FAQ yang berstatus `Active` dihitung nilai **`recommendation_score`**-nya berdasarkan **3 komponen utama**:

$$\text{Total Skor} = \text{Skor Interaksi Historis} + \text{Skor Kesesuaian Fakultas} + \text{Skor Kesesuaian Semester/Angkatan}$$

### A. Komponen 1: Skor Interaksi Historis (`tlogmahasiswa`)
$$\begin{aligned}
\text{Skor Log} &= (\text{View Fakultas \& Angkatan Sama} \times W_{\text{exact}}) \\
&+ (\text{View Angkatan Sama} \times W_{\text{angkatan}}) \\
&+ (\text{View Fakultas Sama} \times W_{\text{fakultas}}) \\
&+ (\text{Total View Global} \times W_{\text{total}}) \\
&+ (\text{Jumlah Like} \times W_{\text{like}}) \\
&+ (\text{Jumlah Dislike} \times W_{\text{dislike}})
\end{aligned}$$

### B. Komponen 2: Skor Kesesuaian Fakultas
Sistem memeriksa apakah kata kunci/pertanyaan FAQ mengandung nama fakultas:
* **Cocok dengan Fakultas mahasiswa** (misal mahasiswa Teknik & FAQ bertag Teknik): **`+15 poin`** (default `rec_bonus_own_fakultas`).
* **Topik Umum / Lintas Fakultas** (tidak spesifik ke fakultas lain): **`+5 poin`** (default `rec_bonus_umum_fakultas`).
* **Milik Fakultas Lain** (misal mahasiswa Teknik tetapi FAQ bertag Kedokteran/Hukum): **`-30 poin`** (default `rec_penalty_other_fakultas`), dan ditandai `is_other_fakultas = true` sehingga tidak masuk tab **Rekomendasi FAQ** (tetap bisa tampil di tab **Top FAQ** atau saat dicari lewat kotak pencarian).

### C. Komponen 3: Skor Kesesuaian Tingkat Angkatan / Semester
Sistem mencocokkan kata kunci/pertanyaan FAQ dengan kebutuhan akademik di semester mahasiswa saat ini:
* **Semester $\ge 7$ (Tingkat Akhir, misal Angkatan 2023):**
  * Topik: `tugas akhir`, `skripsi`, `wisuda`, `yudisium`, `sempro`, `sidang`, `capstone`, `turnitin`, `kelulusan`, `transkrip`, `ijazah`, `legalisir`.
  * Bonus: **`+18 poin`** (default `rec_bonus_angkatan_senior`).
* **Semester 5–6 (Tingkat Menengah Atas, misal Angkatan 2024):**
  * Topik: `kerja praktik`, `magang`, `mbkm`, `praktikum`, `laboratorium`, `lintas prodi`, `beasiswa`, `khs`.
  * Bonus: **`+15 poin`** (default `rec_bonus_angkatan_general`).
* **Semester 3–4 (Tingkat Menengah Awal, misal Angkatan 2025):**
  * Topik: `praktikum`, `laboratorium`, `aslab`, `inhal`, `krs`, `perwalian`, `beasiswa`, `cuti`.
  * Bonus: **`+15 poin`** (default `rec_bonus_angkatan_general`).
* **Semester 1–2 (Mahasiswa Baru, misal Angkatan 2026):**
  * Topik: `registrasi`, `daftar ulang`, `krs`, `perwalian`, `sso`, `wi-fi`, `ukt`, `biaya`, `cicilan`, `umum`.
  * Bonus: **`+15 poin`** (default `rec_bonus_angkatan_general`).

### Contoh Perhitungan Nyata
Mahasiswa masuk dengan NRP **`160423018`** (Fakultas Teknik `16`, Angkatan `2023` $\rightarrow$ Semester 7).  
Ada FAQ **"Syarat Pendaftaran Sidang Tugas Akhir Fakultas Teknik"** yang pernah dilihat **3 kali** oleh mahasiswa Teknik 2023 (`16-23`) dan mendapat **2 Like**:
1. View Segmen Persis (`16-23`): $3 \times 10 = 30$
2. View Angkatan Sama (`-23`): $3 \times 5 = 15$
3. View Fakultas Sama (`16-`): $3 \times 4 = 12$
4. View Global: $3 \times 1 = 3$
5. Like: $2 \times 3 = 6$
6. Bonus Fakultas Teknik: $+15$
7. Bonus Semester 7 (topik Tugas Akhir): $+18$
* **Total Skor Rekomendasi:** $30 + 15 + 12 + 3 + 6 + 15 + 18 = \mathbf{99\text{ poin}}$ (langsung menempati urutan #1 di tab **Rekomendasi FAQ** mahasiswa tersebut).

### Perbedaan Tab `Top FAQ` vs `Rekomendasi FAQ` & Penomoran Visual
Pada halaman FAQ Mahasiswa (`resources/views/mahasiswa/faq.blade.php`), kedua tab tersebut dipisahkan agar jelas fungsinya:
* **Top FAQ (`top`):** Menampilkan **seluruh FAQ** (termasuk semua fakultas) dan diurutkan murni berdasarkan jumlah **`views_count` tertinggi**.
* **Rekomendasi FAQ (`rekomendasi`):** Hanya menampilkan FAQ yang relevan untuk mahasiswa tersebut (`is_other_fakultas = false`, yaitu FAQ umum + fakultas sendiri) dan diurutkan berdasarkan **`recommendation_score` tertinggi** (tie-break: `views_count` tertinggi, lalu ID FAQ).
* **Penomoran Visual (`01`, `02`, `03`, ...):** Diatur ulang secara dinamis oleh JavaScript (`filterFaq()`) setiap kali berpindah tab atau mengetik di pencarian, sehingga nomor urut tampilan selalu rapi mulai dari `01` tanpa melompat.

---

## 3A. Catatan Konsep & Arsitektur (FAQ Teknis)

### 1. Apakah setiap FAQ wajib punya data Fakultas dan Angkatan?
**Tidak.** Tabel `tfaqs` (`App\Models\Faq`) tidak memiliki kolom `fakultas` maupun `angkatan`. Data Entry cukup menuliskan pertanyaan dan memasang `tag` topik seperti biasa (tag nama fakultas hanya perlu ditambahkan jika FAQ memang khusus fakultas tertentu).
* Jika FAQ tidak menyebut nama fakultas apa pun, sistem otomatis menganggapnya **FAQ Umum** (mendapat bonus `rec_bonus_umum_fakultas`) dan tetap masuk tab **Rekomendasi FAQ**.
* Kecocokan semester/angkatan sifatnya bonus tambahan (`0` jika netral, bukan penalti).

### 2. Apakah skor disimpan per fakultas/angkatan di database?
**Tidak.** Yang tersimpan di database hanyalah catatan interaksi mentah (`tlogmahasiswa` + `tsession`). Skor **dihitung secara dinamis (*on-the-fly*)** menyesuaikan NRP mahasiswa yang sedang login:
* Satu FAQ yang sama (misal *Praktikum Lab Teknik*, dibuka 5x oleh FT 2024 dan 2x oleh Psikologi 2025) akan mendapat **skor tinggi** saat dibuka oleh mahasiswa **FT 2024**, tetapi mendapat **skor rendah** saat dibuka oleh mahasiswa **Psikologi 2025**.

### 3. Apakah menghitung tiap reload memberatkan server?
* **Untuk skala saat ini: sangat ringan.** Query ke `tlogmahasiswa` sudah diringkas langsung oleh MySQL menggunakan `GROUP BY`, sehingga PHP hanya mengalikan angka ringkasan untuk puluhan/ratusan FAQ (hitungan milidetik).
* Jika di masa depan jumlah log mencapai ratusan ribu–jutaan baris, optimasi paling praktis adalah menambahkan **cache per segmen** (misal `Cache::remember` per `"kodeFakultas-kodeAngkatan"` selama 5–10 menit, dan dihapus otomatis saat ada perubahan bobot).

### 4. Kenapa tidak menggunakan tabel `tsnapshots`?
Tabel `tsnapshots` (`App\Models\Snapshot`) dirancang untuk **rekap statistik laporan global per periode tanggal** (`periode_awal`, `periode_akhir`, `total_pencarian`, `total_sukses`, `total_gagal`, `total_mhs_aktif`, `total_form_cs`, `top_keywords`), bukan untuk ranking per FAQ karena:
1. Tidak menyimpan `tFaqs_id` maupun jumlah *view/like/dislike* per FAQ.
2. Tidak memisahkan segmen fakultas dan angkatan.
3. Sifatnya rekap periodik, tidak diperbarui otomatis secara *real-time* saat ada interaksi baru atau saat Super Admin mengubah bobot di **Pengaturan Sistem**.

---

## 4. Tabel `tsettings` & Menu "Pengaturan Sistem" (Super Admin)

Seluruh angka bobot di atas, rentang hari pembacaan log, serta waktu *idle auto-logout* mahasiswa **tidak lagi dikunci mati di kode**, melainkan disimpan di tabel **`tsettings`** (`App\Models\Setting`) dan dipisahkan menjadi 2 halaman di **Super Admin** agar ringkas:
1. **Halaman Utama Pengaturan Sistem (`/superadmin/pengaturan-sistem`):** mengatur durasi *idle auto-logout* (`idle_timeout_seconds`, `idle_countdown_seconds`) serta rentang hari log rekomendasi (`rec_log_days`), dilengkapi tombol untuk membuka halaman pengaturan poin.
2. **Halaman Khusus Pengaturan Poin Algoritma (`/superadmin/pengaturan-sistem/poin`):** mengatur 11 bobot poin dan bonus/penalti algoritma rekomendasi.

| Key di `tsettings` | Nilai Default | Fungsi |
| :--- | :---: | :--- |
| `idle_timeout_seconds` | `30` detik | Waktu diam mahasiswa/guest sebelum pop-up konfirmasi muncul |
| `idle_countdown_seconds` | `10` detik | Durasi hitung mundur pada pop-up sebelum otomatis logout ke Portal |
| `rec_log_days` | `30` hari | Rentang waktu log interaksi terbaru (`waktu_pencarian >= N hari terakhir`) yang dihitung untuk rekomendasi FAQ |
| `rec_weight_exact` | `10` | Bobot per 1x View dari Fakultas + Angkatan yang sama persis |
| `rec_weight_angkatan` | `5` | Bobot per 1x View dari Angkatan yang sama |
| `rec_weight_fakultas` | `4` | Bobot per 1x View dari Fakultas yang sama |
| `rec_weight_total` | `1` | Bobot per 1x View Global |
| `rec_weight_like` | `3` | Bobot per 1x Like ("Ya, membantu") |
| `rec_weight_dislike` | `-5` | Penalti per 1x Dislike ("Belum") |
| `rec_bonus_own_fakultas` | `15` | Bonus topik sesuai Fakultas mahasiswa |
| `rec_bonus_umum_fakultas` | `5` | Bonus topik FAQ Umum |
| `rec_penalty_other_fakultas` | `-30` | Penalti topik milik Fakultas lain |
| `rec_bonus_angkatan_senior` | `18` | Bonus topik Tingkat Akhir (Semester 7+) |
| `rec_bonus_angkatan_general` | `15` | Bonus topik sesuai Semester 1–6 |

* Setiap kali Super Admin menekan tombol **"Simpan Perubahan"** atau **"Kembalikan ke Default"**, akan muncul **Pop-up Modal Konfirmasi Ulang** terlebih dahulu agar aman dari salah klik.

---

## 5. Daftar File yang Ditambahkan / Diubah

1. `database/migrations/2026_11_19_000013_create_tsettings_and_update_tlogmahasiswa_table.php` — Migration tabel `tsettings` dan kolom `jenis_interaksi` pada `tlogmahasiswa`.
2. `app/Models/Setting.php` — Model `tsettings` beserta daftar nilai default, auto-init tabel/data default, dan fungsi reset default.
3. `app/Models/LogMahasiswa.php` — Penambahan `jenis_interaksi` (`view`, `like`, `dislike`) dan helper `getFaqInteractionSummary()`.
4. `app/Http/Controllers/PortalController.php` — Parser NRP `1ABBCCDDD`, estimasi semester, pencatatan `view`/`like`/`dislike` anti-duplikat per sesi, dan algoritma `rankFaqsForStudent()` berbasis bobot `tsettings`.
5. `app/Http/Controllers/GuestController.php` — Integrasi ranking FAQ dan pengaturan durasi *idle timeout* dari `tsettings`.
6. `app/Http/Controllers/superadmin/PenggunaAksesController.php` — Statistik Views/Like/Dislike di Dashboard Super Admin serta controller halaman **Pengaturan Sistem** (`pengaturanSistem`, `updatePengaturanSistem`, `resetPengaturanSistem`).
7. `app/Http/Controllers/validator/ValidatorFaqController.php` & `app/Http/Controllers/dataentry/FaqController.php` — Menampilkan statistik Views, Like, dan Dislike di tabel Daftar FAQ dan modal detail.
8. `resources/views/superadmin/pengaturansistem.blade.php` — Halaman UI pengaturan bobot rekomendasi & waktu idle dengan pop-up konfirmasi ulang.
9. `resources/views/superadmin/dashboard.blade.php`, `resources/views/superadmin/pengguna&akses.blade.php`, `resources/views/superadmin/pengaturanakun.blade.php` — Menu sidebar "Pengaturan Sistem" & tabel monitoring Views/Like/Dislike di dashboard.
10. `resources/views/mahasiswa/faq.blade.php`, `resources/views/guest/faq.blade.php`, `resources/views/validator/daftarFAQ.blade.php`, `resources/views/dataentry/daftarFAQ.blade.php` — Tampilan indikator Views/Like/Dislike ala YouTube dan pembacaan waktu idle dinamis.
