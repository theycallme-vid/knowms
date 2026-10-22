<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Data Entry — KMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f9fafb; margin: 0; padding: 40px; color: #111827; }
        .card { background: white; border-radius: 12px; padding: 32px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; color: #059669; }
        .badge { background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 6px; font-size: 13px; font-weight: 600; }
        .btn-logout { background: #111827; color: white; border: none; padding: 10px 18px; border-radius: 6px; cursor: pointer; font-weight: 600; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <span class="badge">Data Entry</span>
        <h1>Selamat Datang, {{ Auth::user()->nama }}!</h1>
        <p>Anda berhasil login ke dashboard <strong>Data Entry</strong>.</p>
        <p><strong>Email:</strong> {{ Auth::user()->email }}</p>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">Logout</button>
        </form>
    </div>
</body>
</html>
