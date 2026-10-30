<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KMS - Login</title>
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/logo/logo-head.png') }}">
    <!-- Pastikan Anda memuat CSS Anda di sini, atau letakkan CSS di bawah pada tag <style> -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Merriweather:wght@700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="split-layout">
        <!-- PANEL KIRI (Sama seperti sebelumnya) -->
        <div class="left-panel">
            <header class="panel-header">
                <img src="{{ asset('images/logo/logo-KMS.png') }}" alt="Logo KMS - Knowledge Management System" class="logo-img">
            </header>

            <main class="panel-content left-content">
                <span class="eyebrow">RUANG KOLABORASI PENGETAHUAN</span>
                <h1 class="heading">Pengetahuan yang baik,<br>dimulai dari <span class="highlight">kita.</span></h1>
                <p class="subheading">Bersama, kita menjaga pengetahuan akademik<br>tetap hidup dan bermakna.</p>
            </main>

            <footer class="panel-footer left-footer">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                <span>Satu sumber pengetahuan. Banyak langkah yang terbantu.</span>
            </footer>
        </div>

        <!-- PANEL KANAN (Form Login) -->
        <div class="right-panel">
            <header class="right-header">
                <a href="#" class="back-link">
                    &larr; Kembali ke portal mahasiswa
                </a>
            </header>

            <main class="panel-content right-content">
                <div class="form-wrapper">
                    <span class="badge">AKSES STAF UBAYA</span>
                    <h2 class="form-heading">Selamat datang kembali.</h2>
                    <p class="form-subheading">Silakan masuk untuk melanjutkan pengelolaan dan berbagi wawasan akademik di ruang lingkup kampus.</p>

                    @if (session('success'))
                        <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 8px; font-size: 14px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    <!-- Form Laravel -->
                    <form action="{{ route('login.submit') }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-icon-wrapper">
                                <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@staff.ubaya.ac.id" required autofocus>
                            </div>
                            @error('email')
                                <small style="color: #b91c1c; font-size: 12px; margin-top: 5px; display: block; font-weight: 500;">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Kata sandi</label>
                            <div class="input-icon-wrapper">
                                <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" required>
                                <button type="button" class="toggle-password" id="btn-toggle-password" title="Lihat kata sandi">
                                    <svg id="icon-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </div>
                            @error('password')
                                <small style="color: #b91c1c; font-size: 12px; margin-top: 5px; display: block; font-weight: 500;">{{ $message }}</small>
                            @enderror
                            <small class="helper-text">Pilih ikon mata untuk menampilkan kata sandi.</small>
                        </div>

                        <div class="form-actions">
                            <label class="remember-me">
                                <input type="checkbox" name="remember" id="remember"> Ingat saya
                            </label>
                            <a href="#" class="forgot-password">Lupa kata sandi?</a>
                        </div>

                        <button type="submit" class="btn-submit">
                            Login
                        </button>
                    </form>
                </div>
            </main>

            <footer class="right-footer">
                &copy; {{ date('Y') }} KMS &middot; <a href="#">Layanan informasi akademik</a> &middot; <a href="#">Kebijakan privasi</a>
            </footer>
        </div>
    </div>

    <script>
        const btnToggle = document.getElementById('btn-toggle-password');
        const inputPassword = document.getElementById('password');
        if (btnToggle && inputPassword) {
            btnToggle.addEventListener('click', function () {
                const isPassword = inputPassword.type === 'password';
                inputPassword.type = isPassword ? 'text' : 'password';
                btnToggle.style.color = isPassword ? '#b91c1c' : '#9ca3af';
            });
        }
    </script>
</body>
</html>