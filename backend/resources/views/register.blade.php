<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Mulai Sekarang - Qandang</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <style>
        :root {
            --primary: #4a6741;
            --primary-dark: #3a5233;
            --accent: #a67c52;
            --secondary: #2d241e;
            --bg-light: #fdfaf5;
            --card-bg: #ffffff;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Instrument Sans', sans-serif;
            background-color: var(--bg-light);
            color: var(--secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            width: 100%;
            background: var(--card-bg);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(74, 103, 65, 0.08);
            text-align: center;
            border: 1px solid rgba(166, 124, 82, 0.1);
        }

        .logo {
            font-weight: 700;
            font-size: 2rem;
            color: var(--primary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }

        h1 { font-size: 1.75rem; margin-bottom: 1rem; }
        p { color: #6b5e51; margin-bottom: 2.5rem; font-size: 1rem; }

        .choice-btn {
            display: block;
            width: 100%;
            padding: 1.2rem;
            margin-bottom: 1rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .btn-register {
            background: var(--primary);
            color: white;
        }

        .btn-register:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-login {
            background: transparent;
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-login:hover {
            background: #f0f4ef;
            transform: translateY(-2px);
        }

        .back-link {
            display: inline-block;
            margin-top: 2rem;
            color: #a67c52;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <div class="container">
        <a href="/" class="logo">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 18l2-2 2 2M12 18l2-2 2 2M9 18l2-2 2 2M16 12l2-2 2 2M12 12l2-2 2 2M9 12l2-2 2 2"/></svg>
            Qandang
        </a>
        
        <h1>Siap Memulai?</h1>
        <p>Pilih opsi di bawah untuk masuk ke sistem monitoring ternak cerdas Anda.</p>

        <a href="/admin/register" class="choice-btn btn-register">Saya Ingin Daftar Baru</a>
        <a href="/admin/login" class="choice-btn btn-login">Saya Sudah Punya Akun</a>

        <a href="/" class="back-link">← Kembali ke Halaman Utama</a>
    </div>

</body>
</html>
