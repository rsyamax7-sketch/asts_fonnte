<?php

session_start();

require_once __DIR__ . "/koneksi.php";
require_once __DIR__ . "/functions/whatsapp.php";

/*
|--------------------------------------------------------------------------
| JIKA SUDAH LOGIN
|--------------------------------------------------------------------------
*/
if (isset($_SESSION['login']) &&$_SESSION['login'] === true) {
    header("Location: index.php");
    exit;
}

$error = "";

/*
|--------------------------------------------------------------------------
| PROSES LOGIN
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password =$_POST['password'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | VALIDASI INPUT
    |--------------------------------------------------------------------------
    */
    if ($username === '' || $password === '') {$error = "Username/email dan password wajib diisi.";
    } else {

        /*
        |--------------------------------------------------------------------------
        | CARI USER
        |--------------------------------------------------------------------------
        */
        $stmt =$koneksi->prepare("
            SELECT
                id,
                nama,
                username,
                email,
                no_wa,
                password
            FROM users_login
            WHERE username = ?
               OR email = ?
            LIMIT 1
        ");

        if (!$stmt) {$error = "Terjadi kesalahan database: " . $koneksi->error;
        } else {

            $stmt->bind_param("ss", $username, $username);$stmt->execute();
            $result =$stmt->get_result();

            if ($result->num_rows === 1) {

                $user =$result->fetch_assoc();

                /*
                |--------------------------------------------------------------------------
                | CEK PASSWORD
                |--------------------------------------------------------------------------
                */
                if (password_verify($password,$user['password'])) {

                    session_regenerate_id(true);

                    $_SESSION['login']    = true;
                    $_SESSION['id']       =$user['id'];
                    $_SESSION['nama']     =$user['nama'];
                    $_SESSION['username'] =$user['username'];
                    $_SESSION['email']    =$user['email'];
                    $_SESSION['no_wa']    =$user['no_wa'] ?? '';

                    /*
                    |--------------------------------------------------------------------------
                    | PESAN WHATSAPP LOGIN
                    |--------------------------------------------------------------------------
                    */
                    $pesan = 
"📌 *NOTIFIKASI LOGIN SISTEM*

Halo *{$user['nama']}*,

Akun Anda baru saja login ke Sistem Pendataan Siswa ASTS.

👤 Username: {$user['username']}
📧 Email: {$user['email']}
🕐 Waktu: " . date('d-m-Y H:i:s') . "

Jika aktivitas ini bukan dilakukan oleh Anda, segera amankan akun Anda atau hubungi administrator.";

                    $nomorWA = trim($user['no_wa'] ?? '');

                    if ($nomorWA === '' && defined('FONNTE_TARGET')) {$nomorWA = FONNTE_TARGET;
                    }

                    if ($nomorWA !== '') {
                        try {
                            $hasilWA = kirimWhatsApp($nomorWA,$pesan);

                            if (is_array($hasilWA) && isset($hasilWA['status']) && $hasilWA['status'] === true) {$_SESSION['wa_notif'] = [
                                    'status' => true,
                                    'message' => 'Login berhasil dan notifikasi WhatsApp telah dikirim.'
                                ];
                                unset($_SESSION['wa_error']);
                            } else {
                                $_SESSION['wa_notif'] = [
                                    'status' => false,
                                    'message' => 'Login berhasil, namun notifikasi WhatsApp gagal terkirim.'
                                ];
                                $_SESSION['wa_error'] =$hasilWA['response'] ?? 'Tidak ada respon dari layanan WhatsApp.';
                            }
                        } catch (Throwable $e) {$_SESSION['wa_notif'] = [
                                'status' => false,
                                'message' => 'Login berhasil, terjadi kesalahan sistem pengiriman WhatsApp.'
                            ];
                            $_SESSION['wa_error'] =$e->getMessage();
                        }
                    } else {
                        $_SESSION['wa_notif'] = [                             'status' => false,                             'message' => 'Login berhasil, nomor WhatsApp tidak tersedia.'                         ];$_SESSION['wa_error'] = 'Nomor WhatsApp user kosong.';
                    }

                    $stmt->close();
                    header("Location: index.php");
                    exit;

                } else {
                    $error = "Password yang Anda masukkan salah.";
                }

            } else {
                $error = "Username atau email tidak terdaftar.";
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ASTS Portal</title>
    
    <!-- Google Fonts Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-grad: linear-gradient(135deg, #6366f1 0%, #a855f7 50%, #ec4899 100%);
            --btn-hover-grad: linear-gradient(135deg, #4f46e5 0%, #9333ea 50%, #db2777 100%);
            --bg-main: #0f172a;
            --card-bg: rgba(255, 255, 255, 0.92);
            --text-title: #0f172a;
            --text-body: #475569;
            --input-border: #cbd5e1;
            --input-focus: #818cf8;
            --error-bg: #fef2f2;
            --error-border: #fca5a5;
            --error-text: #991b1b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 40%, #c026d3 70%, #f43f5e 100%);
            background-size: 200% 200%;
            animation: gradientBG 12s ease infinite;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Ambient Glow Shapes */
        .glow-1, .glow-2 {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.5;
            pointer-events: none;
        }
        .glow-1 {
            width: 300px;
            height: 300px;
            background: #38bdf8;
            top: -50px;
            left: -50px;
        }
        .glow-2 {
            width: 350px;
            height: 350px;
            background: #f43f5e;
            bottom: -50px;
            right: -50px;
        }

        .container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 10;
        }

        .logo-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .logo-badge {
            width: 64px;
            height: 64px;
            margin: 0 auto 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
            color: #ffffff;
        }

        .logo-badge svg {
            width: 32px;
            height: 32px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .logo-header h1 {
            font-size: 26px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.02em;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .logo-header p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
            font-weight: 500;
            margin-top: 2px;
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 24px;
            padding: 36px 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .card-header {
            margin-bottom: 24px;
        }

        .card-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-title);
        }

        .card-header p {
            font-size: 13px;
            color: var(--text-body);
            margin-top: 4px;
        }

        .alert-error {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background-color: var(--error-bg);
            border: 1px solid var(--error-border);
            border-radius: 12px;
            color: var(--error-text);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.08);
        }

        .alert-error svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            fill: currentColor;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        input {
            width: 100%;
            padding: 13px 16px;
            font-size: 14px;
            font-weight: 500;
            border: 2px solid var(--input-border);
            border-radius: 12px;
            background-color: #ffffff;
            color: var(--text-title);
            outline: none;
            transition: all 0.2s ease;
        }

        input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.18);
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: #6366f1;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--primary-grad);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
            transition: all 0.25s ease;
            margin-top: 6px;
        }

        .btn-submit:hover {
            background: var(--btn-hover-grad);
            box-shadow: 0 12px 24px -4px rgba(168, 85, 247, 0.5);
            transform: translateY(-1px);
        }

        .btn-submit:active {
            transform: translateY(1px);
            box-shadow: 0 6px 12px -2px rgba(99, 102, 241, 0.4);
        }

        .card-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 13.5px;
            color: var(--text-body);
            font-weight: 500;
        }

        .card-footer a {
            color: #7c3aed;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s;
        }

        .card-footer a:hover {
            color: #ec4899;
            text-decoration: underline;
        }

        .page-footer {
            text-align: center;
            margin-top: 28px;
            font-size: 12.5px;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
            text-shadow: 0 1px 4px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>

<div class="glow-1"></div>
<div class="glow-2"></div>

<div class="container">

    <div class="logo-header">
        <div class="logo-badge">
            <svg viewBox="0 0 24 24">
                <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
            </svg>
        </div>
        <h1>ASTS Portal</h1>
        <p>Sistem Pendataan Siswa</p>
    </div>

    <div class="card">

        <div class="card-header">
            <h2>Selamat Datang Kembali 👋</h2>
            <p>Silakan masuk dengan akun Anda untuk melanjutkan</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert-error">
                <svg viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" autocomplete="off">

            <div class="form-group">
                <label for="username">Username atau Email</label>
                <div class="input-wrapper">
                    <input 
                        type="text" 
                        id="username"
                        name="username" 
                        placeholder="Masukkan username/email" 
                        autocomplete="username"
                        required
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input 
                        type="password" 
                        id="password"
                        name="password" 
                        placeholder="Masukkan password" 
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="toggle-password" id="togglePassword" aria-label="Tampilkan password">
                        <svg id="eyeIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                Masuk Sekarang
            </button>

        </form>

        <div class="card-footer">
            Belum punya akun? <a href="register.php">Daftar Akun Baru</a>
        </div>

    </div>

    <div class="page-footer">
        &copy; <?= date('Y') ?> ASTS Portal System • All Rights Reserved
    </div>

</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    const eyeIcon = document.querySelector('#eyeIcon');

    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        if (type === 'text') {
            eyeIcon.innerHTML = `
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
            `;
        } else {
            eyeIcon.innerHTML = `
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            `;
        }
    });
</script>

</body>
</html>