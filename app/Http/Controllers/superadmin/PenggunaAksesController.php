<?php

namespace App\Http\Controllers\superadmin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class PenggunaAksesController extends Controller
{
    /**
     * Menampilkan daftar pengguna dan hak akses.
     */
    public function index()
    {
        try {
            $users = User::with('role')->orderBy('id', 'desc')->get();
            $roles = Role::all();
        } catch (Throwable $e) {
            Log::warning('Database belum terhubung atau tabel belum siap: ' . $e->getMessage());
            // Fallback dataset jika database offline/belum di-migrate
            $users = collect($this->getFallbackUsers());
            $roles = collect($this->getFallbackRoles());
        }

        return view('superadmin.pengguna&akses', compact('users', 'roles'));
    }

    /**
     * Menyimpan pengguna baru ke tabel tusers.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama'      => 'required|string|max:100',
            'email'     => 'required|email|max:50|unique:tusers,email',
            'npk'       => 'nullable|string|max:15|unique:tusers,npk',
            'password'  => 'required|string|min:8',
            'tRoles_id' => 'required|integer',
            'is_active' => 'nullable|boolean',
        ], [
            'password.required' => 'Kata sandi awal wajib diisi.',
            'password.min'      => 'Kata sandi awal minimal harus 8 karakter.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah terdaftar di sistem.',
            'nama.required'     => 'Nama lengkap wajib diisi.',
            'tRoles_id.required'=> 'Pemberian role wajib dipilih.',
        ]);

        try {
            User::create([
                'nama'      => $request->nama,
                'email'     => $request->email,
                'npk'       => $request->npk ?: null,
                'password'  => Hash::make($request->password),
                'tRoles_id' => $request->tRoles_id,
                'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
            ]);

            return redirect()->back()->with('success', 'Pengguna baru berhasil ditambahkan.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan pengguna: ' . $e->getMessage());
        }
    }

    /**
     * Memperbarui keterangan pengguna (nama, email, npk, status, password opsional).
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama'      => 'required|string|max:100',
            'email'     => 'required|email|max:50|unique:tusers,email,' . $id,
            'npk'       => 'nullable|string|max:15|unique:tusers,npk,' . $id,
            'password'  => 'nullable|string|min:8',
            'is_active' => 'nullable|boolean',
        ], [
            'password.min'      => 'Kata sandi baru minimal harus 8 karakter.',
            'email.required'    => 'Email wajib diisi.',
            'email.unique'      => 'Email sudah terdaftar oleh staf lain.',
            'nama.required'     => 'Nama lengkap wajib diisi.',
        ]);

        try {
            $user = User::findOrFail($id);

            $data = [
                'nama'      => $request->nama,
                'email'     => $request->email,
                'npk'       => $request->npk ?: null,
                'is_active' => $request->has('is_active') ? (bool)$request->is_active : false,
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            return redirect()->back()->with('success', 'Keterangan pengguna "' . $user->nama . '" berhasil diperbarui.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui pengguna: ' . $e->getMessage());
        }
    }

    /**
     * Menugaskan/mengganti role pengguna (Add / Update Role).
     */
    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'tRoles_id' => 'required|integer|exists:troles,id',
        ]);

        try {
            $user = User::findOrFail($id);

            // Syarat ketat 1: Jangan biarkan superadmin sendiri kehilangan role superadmin tanpa konfirmasi
            $currentUserId = Auth::id() ?? 1;
            if ($user->id == $currentUserId && $user->tRoles_id == 1 && $request->tRoles_id != 1) {
                return redirect()->back()->with('error', 'Proteksi Sistem: Anda tidak diizinkan mendegradasi peran Super Admin pada akun Anda sendiri.');
            }

            // Syarat ketat 2: Cek kuorum Super Admin
            if ($user->tRoles_id == 1 && $request->tRoles_id != 1) {
                $adminCount = User::where('tRoles_id', 1)->where('is_active', true)->count();
                if ($adminCount <= 1) {
                    return redirect()->back()->with('error', 'Proteksi Sistem: Sistem harus memiliki setidaknya satu Super Admin aktif. Perubahan peran ditolak.');
                }
            }

            $user->update([
                'tRoles_id' => $request->tRoles_id,
            ]);

            return redirect()->back()->with('success', 'Peran untuk "' . $user->nama . '" berhasil diperbarui.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Gagal mengubah peran: ' . $e->getMessage());
        }
    }

    /**
     * Mencabut role pengguna dengan syarat yang ketat (Strict Remove Role).
     */
    public function removeRole(Request $request, $id)
    {
        $request->validate([
            'konfirmasi_teks' => 'required|string',
        ]);

        if (strtoupper(trim($request->konfirmasi_teks)) !== 'CABUT PERAN') {
            return redirect()->back()->with('error', 'Konfirmasi teks tidak cocok. Masukkan kata "CABUT PERAN" dengan benar.');
        }

        try {
            $user = User::findOrFail($id);
            $currentUserId = Auth::id() ?? 1;

            // Syarat Ketat 1: Akun diri sendiri dilarang dicabut
            if ($user->id == $currentUserId) {
                return redirect()->back()->with('error', 'Akses Ditolak: Anda tidak dapat mencabut hak akses pada akun yang sedang Anda gunakan.');
            }

            // Syarat Ketat 2: Kuorum Super Admin aktif
            if ($user->tRoles_id == 1) {
                $activeSuperAdmins = User::where('tRoles_id', 1)->where('is_active', true)->count();
                if ($activeSuperAdmins <= 1) {
                    return redirect()->back()->with('error', 'Proteksi Kuorum: User ini adalah Super Admin aktif terakhir. Pencabutan role dilarang.');
                }
            }

            // Nonaktifkan user dan kosongkan wewenang / nonaktifkan akses
            $user->update([
                'is_active' => false,
            ]);

            return redirect()->back()->with('success', 'Hak akses untuk "' . $user->nama . '" telah dicabut dan status akun dinonaktifkan demi keamanan.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Gagal mencabut role: ' . $e->getMessage());
        }
    }

    /**
     * Toggle status aktif/nonaktif akun.
     */
    public function toggleStatus($id)
    {
        try {
            $user = User::findOrFail($id);
            $currentUserId = Auth::id() ?? 1;

            if ($user->id == $currentUserId && $user->is_active) {
                return redirect()->back()->with('error', 'Proteksi Sistem: Anda tidak dapat menonaktifkan akun Anda sendiri.');
            }

            $user->update(['is_active' => !$user->is_active]);

            $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
            return redirect()->back()->with('success', 'Akun "' . $user->nama . '" berhasil ' . $statusText . '.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    /**
     * Fallback mock data jika database belum terkoneksi.
     */
    private function getFallbackRoles()
    {
        return [
            (object)['id' => 1, 'nama' => 'Super Admin', 'created_at' => now()],
            (object)['id' => 2, 'nama' => 'Data Entry', 'created_at' => now()],
            (object)['id' => 3, 'nama' => 'Validator', 'created_at' => now()],
        ];
    }

    private function getFallbackUsers()
    {
        $roleAdmin = (object)['id' => 1, 'nama' => 'Super Admin'];
        $roleDataEntry = (object)['id' => 2, 'nama' => 'Data Entry'];
        $roleValidator = (object)['id' => 3, 'nama' => 'Validator'];

        return [
            (object)[
                'id' => 1,
                'nama' => 'Dimas Pratama (Admin KMS)',
                'email' => 'adminkms@gmail.com',
                'npk' => 'ADM-2024-001',
                'tRoles_id' => 1,
                'role' => $roleAdmin,
                'is_active' => true,
                'created_at' => now()->subMonths(6),
                'updated_at' => now()->subDays(2),
            ],
            (object)[
                'id' => 2,
                'nama' => 'Siti Rahmawati',
                'email' => 'siti.rahma@campus.ac.id',
                'npk' => 'DTE-2024-012',
                'tRoles_id' => 2,
                'role' => $roleDataEntry,
                'is_active' => true,
                'created_at' => now()->subMonths(4),
                'updated_at' => now()->subDays(5),
            ],
            (object)[
                'id' => 3,
                'nama' => 'Dr. Hendra Wijaya',
                'email' => 'hendra.wijaya@campus.ac.id',
                'npk' => 'VAL-2024-003',
                'tRoles_id' => 3,
                'role' => $roleValidator,
                'is_active' => true,
                'created_at' => now()->subMonths(5),
                'updated_at' => now()->subDay(),
            ],
            (object)[
                'id' => 4,
                'nama' => 'Anisa Maharani',
                'email' => 'anisa.m@campus.ac.id',
                'npk' => 'DTE-2024-018',
                'tRoles_id' => 2,
                'role' => $roleDataEntry,
                'is_active' => true,
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subDays(7),
            ],
            (object)[
                'id' => 5,
                'nama' => 'Budi Santoso',
                'email' => 'budi.santoso@campus.ac.id',
                'npk' => 'VAL-2024-008',
                'tRoles_id' => 3,
                'role' => $roleValidator,
                'is_active' => false,
                'created_at' => now()->subMonths(2),
                'updated_at' => now()->subDays(10),
            ],
        ];
    }
}
