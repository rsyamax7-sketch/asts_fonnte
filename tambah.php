<?php

require_once "koneksi.php";
require_once __DIR__ . "/functions/whatsapp.php";

$error = "";

$name = "";
$nisn = "";
$ttl = "";
$gender = "";
$email = "";
$address = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $nisn = trim($_POST['nisn'] ?? '');
    $ttl = trim($_POST['ttl'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | VALIDASI
    |--------------------------------------------------------------------------
    */

    if (
        $name === '' ||
        $nisn === '' ||
        $ttl === '' ||
        $gender === '' ||
        $email === '' ||
        $address === ''
    ) {

        $error = "Semua data wajib diisi.";

    } elseif (!preg_match('/^[0-9]{1,10}$/', $nisn)) {

        $error = "NISN harus berupa angka maksimal 10 digit.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Format email tidak valid.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | INSERT DATA
        |--------------------------------------------------------------------------
        */

        $query = "
            INSERT INTO users
            (
                name,
                nisn,
                ttl,
                gender,
                email,
                address
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ";

        $stmt = $koneksi->prepare($query);

        if (!$stmt) {

            $error = "Query gagal dibuat: " . $koneksi->error;

        } else {

            $stmt->bind_param(
                "ssssss",
                $name,
                $nisn,
                $ttl,
                $gender,
                $email,
                $address
            );

            if ($stmt->execute()) {

                $idSiswa = $stmt->insert_id;

                $stmt->close();

                /*
                |--------------------------------------------------------------------------
                | PESAN WHATSAPP
                |--------------------------------------------------------------------------
                */

                $pesan =
"📚 *ASTS DATA SISWA*

━━━━━━━━━━━━━━━━━━
📢 *NOTIFIKASI DATA BARU*
━━━━━━━━━━━━━━━━━━

👤 Nama:
{$name}

🆔 ID:
{$idSiswa}

🔢 NISN:
{$nisn}

🎂 TTL:
{$ttl}

⚧ Gender:
{$gender}

📧 Email:
{$email}

🏠 Alamat:
{$address}

━━━━━━━━━━━━━━━━━━
✅ *DATA SISWA BERHASIL DITAMBAHKAN*
━━━━━━━━━━━━━━━━━━

🕐 Waktu:
" . date('d-m-Y H:i:s') . "

━━━━━━━━━━━━━━━━━━
📚 ASTS DATA SYSTEM
━━━━━━━━━━━━━━━━━━";

                /*
                |--------------------------------------------------------------------------
                | KIRIM WHATSAPP
                |--------------------------------------------------------------------------
                */

                $hasilWA = null;

                try {

                    /*
                    | Jika FONNTE_TARGET tersedia,
                    | kirim ke nomor tersebut.
                    */

                    if (defined('FONNTE_TARGET')) {

                        $hasilWA = kirimWhatsApp(
                            FONNTE_TARGET,
                            $pesan
                        );

                    } else {

                        $hasilWA = [
                            'status' => false,
                            'response' => 'FONNTE_TARGET belum didefinisikan.'
                        ];

                    }

                } catch (Throwable $e) {

                    $hasilWA = [
                        'status' => false,
                        'response' => $e->getMessage()
                    ];
                }


                /*
                |--------------------------------------------------------------------------
                | SIMPAN NOTIFIKASI
                |--------------------------------------------------------------------------
                */

                if (
                    is_array($hasilWA) &&
                    isset($hasilWA['status']) &&
                    $hasilWA['status'] === true
                ) {

                    $_SESSION['wa_notif'] = [
                        'status' => true,
                        'message' =>
                            'Data siswa berhasil ditambahkan dan notifikasi WhatsApp berhasil dikirim.'
                    ];

                    unset($_SESSION['wa_error']);

                } else {

                    $_SESSION['wa_notif'] = [
                        'status' => false,
                        'message' =>
                            'Data siswa berhasil ditambahkan, tetapi notifikasi WhatsApp gagal dikirim.'
                    ];

                    $_SESSION['wa_error'] =
                        is_array($hasilWA)
                            ? (
                                $hasilWA['response']
                                ?? 'Tidak ada response dari Fonnte.'
                            )
                            : (
                                $hasilWA
                                ?? 'Tidak ada response dari Fonnte.'
                            );
                }


                /*
                |--------------------------------------------------------------------------
                | KEMBALI KE INDEX
                |--------------------------------------------------------------------------
                */

                header("Location: index.php");
                exit;

            } else {

                $error =
                    "Data gagal disimpan: " .
                    $stmt->error;

                $stmt->close();
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Tambah Data Siswa | ASTS</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {

            font-family:
                'Segoe UI',
                Arial,
                sans-serif;

            min-height: 100vh;

            color: #1e293b;

            background:
                linear-gradient(
                    135deg,
                    #eef2ff,
                    #f8fafc,
                    #f5f3ff
                );

            padding: 35px 20px;
        }


        /* =========================
           BACKGROUND DECORATION
        ========================= */

        body::before {

            content: "";

            position: fixed;

            width: 350px;
            height: 350px;

            background:
                rgba(99,102,241,.15);

            border-radius: 50%;

            top: -120px;
            left: -100px;

            filter: blur(5px);

            z-index: -1;
        }


        body::after {

            content: "";

            position: fixed;

            width: 400px;
            height: 400px;

            background:
                rgba(168,85,247,.12);

            border-radius: 50%;

            right: -150px;
            bottom: -150px;

            filter: blur(5px);

            z-index: -1;
        }


        /* =========================
           CONTAINER
        ========================= */

        .container {

            max-width: 900px;

            margin: auto;
        }


        /* =========================
           TOP
        ========================= */

        .top {

            display: flex;

            align-items: center;

            gap: 15px;

            margin-bottom: 25px;
        }


        .back {

            width: 46px;
            height: 46px;

            display: flex;

            align-items: center;
            justify-content: center;

            text-decoration: none;

            color: #4f46e5;

            background: white;

            border: 1px solid #e2e8f0;

            border-radius: 12px;

            font-size: 22px;

            box-shadow:
                0 5px 15px
                rgba(15,23,42,.08);

            transition: .2s;
        }


        .back:hover {

            color: white;

            background:
                linear-gradient(
                    135deg,
                    #6366f1,
                    #8b5cf6
                );

            transform:
                translateX(-3px);
        }


        .top h1 {

            color: #312e81;

            font-size: 26px;

            margin-bottom: 4px;
        }


        .top p {

            color: #64748b;

            font-size: 13px;
        }


        /* =========================
           FORM CARD
        ========================= */

        .form-card {

            background: rgba(255,255,255,.96);

            border: 1px solid #e2e8f0;

            border-top:
                5px solid #6366f1;

            border-radius: 18px;

            padding: 35px;

            box-shadow:
                0 15px 40px
                rgba(15,23,42,.10);
        }


        /* =========================
           FORM HEADER
        ========================= */

        .form-heading {

            display: flex;

            align-items: center;

            gap: 15px;

            padding-bottom: 25px;

            border-bottom:
                1px solid #e2e8f0;

            margin-bottom: 28px;
        }


        .form-icon {

            width: 58px;
            height: 58px;

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 27px;

            color: white;

            border-radius: 15px;

            background:
                linear-gradient(
                    135deg,
                    #6366f1,
                    #8b5cf6
                );

            box-shadow:
                0 8px 20px
                rgba(99,102,241,.25);
        }


        .form-heading h2 {

            font-size: 20px;

            color: #1e293b;
        }


        .form-heading p {

            color: #64748b;

            font-size: 12px;

            margin-top: 5px;
        }


        /* =========================
           ERROR
        ========================= */

        .error {

            display: flex;

            align-items: center;

            gap: 10px;

            background: #fef2f2;

            border:
                1px solid #fecaca;

            color: #b91c1c;

            padding: 13px 15px;

            border-radius: 10px;

            margin-bottom: 22px;

            font-size: 13px;
        }


        /* =========================
           FORM GRID
        ========================= */

        .form-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 22px;
        }


        .form-group {

            display: flex;

            flex-direction: column;

            gap: 8px;
        }


        .full {

            grid-column: 1 / -1;
        }


        label {

            font-size: 12px;

            font-weight: 700;

            color: #334155;
        }


        .required {

            color: #ef4444;
        }


        input,
        select,
        textarea {

            width: 100%;

            padding: 13px 15px;

            border:
                1px solid #cbd5e1;

            border-radius: 10px;

            background: #f8fafc;

            color: #1e293b;

            font-family: inherit;

            font-size: 13px;

            outline: none;

            transition: .2s;
        }


        input::placeholder,
        textarea::placeholder {

            color: #94a3b8;
        }


        input:focus,
        select:focus,
        textarea:focus {

            background: white;

            border-color: #6366f1;

            box-shadow:
                0 0 0 3px
                rgba(99,102,241,.12);
        }


        select option {

            background: white;

            color: #1e293b;
        }


        textarea {

            min-height: 115px;

            resize: vertical;
        }


        .hint {

            color: #94a3b8;

            font-size: 11px;
        }


        /* =========================
           BUTTON
        ========================= */

        .buttons {

            display: flex;

            justify-content: flex-end;

            gap: 12px;

            border-top:
                1px solid #e2e8f0;

            padding-top: 25px;

            margin-top: 30px;
        }


        .btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 7px;

            padding: 13px 22px;

            border-radius: 10px;

            font-size: 13px;

            font-weight: 700;

            text-decoration: none;

            cursor: pointer;

            transition: .2s;
        }


        .btn-kembali {

            background: #f1f5f9;

            color: #475569;

            border:
                1px solid #e2e8f0;
        }


        .btn-kembali:hover {

            background: #e2e8f0;

            transform:
                translateY(-2px);
        }


        .btn-simpan {

            border: none;

            color: white;

            background:
                linear-gradient(
                    135deg,
                    #4f46e5,
                    #7c3aed
                );

            box-shadow:
                0 7px 18px
                rgba(79,70,229,.25);
        }


        .btn-simpan:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 10px 25px
                rgba(79,70,229,.35);
        }


        /* =========================
           FOOTER
        ========================= */

        .space-footer {

            text-align: center;

            color: #94a3b8;

            font-size: 11px;

            margin-top: 20px;

            letter-spacing: .5px;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 650px) {

            body {

                padding:
                    20px 15px;
            }


            .form-card {

                padding: 22px;

                border-radius: 15px;
            }


            .form-grid {

                grid-template-columns: 1fr;

                gap: 18px;
            }


            .full {

                grid-column: auto;
            }


            .buttons {

                flex-direction: column-reverse;
            }


            .btn {

                width: 100%;
            }


            .top h1 {

                font-size: 21px;
            }

        }

    </style>

</head>


<body>

<div class="container">


    <!-- HEADER -->

    <div class="top">

        <a
            href="index.php"
            class="back"
        >
            ←
        </a>

        <div>

            <h1>
                Tambah Data Siswa
            </h1>

            <p>
                Tambahkan data siswa baru ke dalam sistem.
            </p>

        </div>

    </div>


    <!-- FORM CARD -->

    <div class="form-card">


        <div class="form-heading">

            <div class="form-icon">
                👨‍🎓
            </div>

            <div>

                <h2>
                    Informasi Data Siswa
                </h2>

                <p>
                    Lengkapi semua informasi siswa dengan benar.
                </p>

            </div>

        </div>


        <!-- ERROR -->

        <?php if ($error !== ''): ?>

            <div class="error">

                ⚠️

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            autocomplete="off"
        >


            <div class="form-grid">


                <!-- NAMA -->

                <div class="form-group full">

                    <label>

                        Nama Lengkap

                        <span class="required">
                            *
                        </span>

                    </label>

                    <input
                        type="text"
                        name="name"
                        value="<?= htmlspecialchars($name) ?>"
                        placeholder="Masukkan nama lengkap siswa"
                        required
                    >

                </div>


                <!-- NISN -->

                <div class="form-group">

                    <label>

                        NISN

                        <span class="required">
                            *
                        </span>

                    </label>

                    <input
                        type="text"
                        name="nisn"
                        value="<?= htmlspecialchars($nisn) ?>"
                        placeholder="Masukkan NISN"
                        maxlength="10"
                        pattern="[0-9]{1,10}"
                        oninput="
                            this.value =
                            this.value
                            .replace(/[^0-9]/g,'')
                            .slice(0,10)
                        "
                        required
                    >

                    <span class="hint">
                        Maksimal 10 digit angka.
                    </span>

                </div>


                <!-- GENDER -->

                <div class="form-group">

                    <label>

                        Gender

                        <span class="required">
                            *
                        </span>

                    </label>

                    <select
                        name="gender"
                        required
                    >

                        <option value="">
                            Pilih Gender
                        </option>

                        <option
                            value="MALE"
                            <?= $gender === 'MALE'
                                ? 'selected'
                                : '' ?>
                        >
                            MALE
                        </option>

                        <option
                            value="FEMALE"
                            <?= $gender === 'FEMALE'
                                ? 'selected'
                                : '' ?>
                        >
                            FEMALE
                        </option>

                    </select>

                </div>


                <!-- TTL -->

                <div class="form-group full">

                    <label>

                        Tempat, Tanggal Lahir

                        <span class="required">
                            *
                        </span>

                    </label>

                    <input
                        type="text"
                        name="ttl"
                        value="<?= htmlspecialchars($ttl) ?>"
                        placeholder="Contoh: Sumenep, 12 Agustus 2010"
                        required
                    >

                </div>


                <!-- EMAIL -->

                <div class="form-group full">

                    <label>

                        Email

                        <span class="required">
                            *
                        </span>

                    </label>

                    <input
                        type="email"
                        name="email"
                        value="<?= htmlspecialchars($email) ?>"
                        placeholder="nama@email.com"
                        required
                    >

                </div>


                <!-- ADDRESS -->

                <div class="form-group full">

                    <label>

                        Alamat Lengkap

                        <span class="required">
                            *
                        </span>

                    </label>

                    <textarea
                        name="address"
                        placeholder="Masukkan alamat lengkap siswa..."
                        required
                    ><?= htmlspecialchars($address) ?></textarea>

                </div>


            </div>


            <!-- BUTTON -->

            <div class="buttons">

                <a
                    href="index.php"
                    class="btn btn-kembali"
                >
                    ← Batal
                </a>


                <button
                    type="submit"
                    name="simpan"
                    class="btn btn-simpan"
                >
                    💾 Simpan Data
                </button>

            </div>


        </form>

    </div>


    <div class="space-footer">

        ASTS DATA SYSTEM • © 2026

    </div>


</div>

</body>

</html>