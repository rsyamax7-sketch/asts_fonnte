<?php

session_start();

/*
|--------------------------------------------------------------------------
| CEK LOGIN
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['login']) ||
    $_SESSION['login'] !== true
) {
    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| LOAD DATABASE DAN WHATSAPP
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/koneksi.php";
require_once __DIR__ . "/functions/whatsapp.php";


/*
|--------------------------------------------------------------------------
| AMBIL ID
|--------------------------------------------------------------------------
*/

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($id <= 0) {
    die("ID data tidak valid.");
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA SISWA
|--------------------------------------------------------------------------
*/

$stmt = $koneksi->prepare(
    "SELECT * FROM users WHERE id = ?"
);

if (!$stmt) {
    die("Gagal membuat query: " . $koneksi->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$data = $result->fetch_assoc();

$stmt->close();


if (!$data) {
    die("Data siswa dengan ID $id tidak ditemukan.");
}


/*
|--------------------------------------------------------------------------
| VARIABEL
|--------------------------------------------------------------------------
*/

$error = "";


/*
|--------------------------------------------------------------------------
| PROSES UPDATE
|--------------------------------------------------------------------------
*/

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

        $error = "Semua data harus diisi.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Format email tidak valid.";

    } else {


        /*
        |--------------------------------------------------------------------------
        | UPDATE DATA
        |--------------------------------------------------------------------------
        */

        $query = "
            UPDATE users SET
                name = ?,
                nisn = ?,
                ttl = ?,
                gender = ?,
                email = ?,
                address = ?
            WHERE id = ?
        ";

        $stmt = $koneksi->prepare($query);

        if (!$stmt) {

            $error =
                "Query update gagal: " .
                $koneksi->error;

        } else {

            $stmt->bind_param(
                "ssssssi",
                $name,
                $nisn,
                $ttl,
                $gender,
                $email,
                $address,
                $id
            );


            /*
            |--------------------------------------------------------------------------
            | JALANKAN UPDATE
            |--------------------------------------------------------------------------
            */

            if ($stmt->execute()) {

                $stmt->close();


                /*
                |--------------------------------------------------------------------------
                | NAMA ADMIN
                |--------------------------------------------------------------------------
                */

                $namaAdmin =
                    $_SESSION['nama'] ??
                    'Administrator';


                /*
                |--------------------------------------------------------------------------
                | PESAN WHATSAPP
                |--------------------------------------------------------------------------
                */

                $pesan =
"✏️ *ASTS - SISTEM DATA SISWA*

━━━━━━━━━━━━━━━━━━
📢 *NOTIFIKASI EDIT DATA*
━━━━━━━━━━━━━━━━━━

👤 Nama:
{$name}

🆔 ID:
{$id}

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
✅ *DATA BERHASIL DIPERBARUI*
━━━━━━━━━━━━━━━━━━

👨‍💻 Diubah oleh:
{$namaAdmin}

🕐 Waktu:
" . date('d-m-Y H:i:s') . "

━━━━━━━━━━━━━━━━━━
📚 SISTEM DATA SISWA
━━━━━━━━━━━━━━━━━━";


                /*
                |--------------------------------------------------------------------------
                | KIRIM WHATSAPP
                |--------------------------------------------------------------------------
                */

                try {

                    $hasilWA = kirimWhatsApp(
                        FONNTE_TARGET,
                        $pesan
                    );

                } catch (Throwable $e) {

                    $hasilWA = [
                        'status' => false,
                        'response' => $e->getMessage()
                    ];
                }


                /*
                |--------------------------------------------------------------------------
                | CEK HASIL WHATSAPP
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
                            'Data berhasil diperbarui dan notifikasi WhatsApp berhasil dikirim.'

                    ];

                    unset($_SESSION['wa_error']);

                } else {

                    $_SESSION['wa_notif'] = [

                        'status' => false,

                        'message' =>
                            'Data berhasil diperbarui, tetapi notifikasi WhatsApp gagal dikirim.'

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
                    "Data gagal diperbarui: " .
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

    <title>Edit Data Siswa</title>


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        body {

            font-family:
                'Segoe UI',
                Arial,
                sans-serif;

            min-height: 100vh;

            background:
                #f1f5f9;

            color:
                #1e293b;

            padding:
                35px 20px;
        }


        .container {

            width: 100%;

            max-width: 850px;

            margin: auto;
        }


        /* HEADER */

        .page-header {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 25px;
        }


        .header-left {

            display: flex;

            align-items: center;

            gap: 15px;
        }


        .header-icon {

            width: 52px;

            height: 52px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 12px;

            background:
                #e0e7ff;

            color:
                #4f46e5;

            font-size: 23px;
        }


        .page-header h1 {

            font-size: 25px;

            color:
                #0f172a;

            margin-bottom: 4px;
        }


        .page-header p {

            color:
                #64748b;

            font-size: 13px;
        }


        .back-top {

            text-decoration: none;

            background:
                white;

            border:
                1px solid #e2e8f0;

            color:
                #475569;

            padding:
                10px 15px;

            border-radius:
                8px;

            font-size:
                13px;

            font-weight:
                600;

            transition:
                .2s;
        }


        .back-top:hover {

            background:
                #f8fafc;

            border-color:
                #cbd5e1;
        }


        /* CARD */

        .card {

            background:
                white;

            border:
                1px solid #e2e8f0;

            border-radius:
                14px;

            box-shadow:
                0 8px 25px
                rgba(15, 23, 42, .06);

            overflow:
                hidden;
        }


        .card-header {

            padding:
                22px 28px;

            border-bottom:
                1px solid #e2e8f0;

            background:
                #ffffff;
        }


        .card-header h2 {

            font-size:
                17px;

            color:
                #0f172a;

            margin-bottom:
                5px;
        }


        .card-header p {

            font-size:
                12px;

            color:
                #64748b;
        }


        .card-body {

            padding:
                28px;
        }


        /* ERROR */

        .error {

            display:
                flex;

            align-items:
                flex-start;

            gap:
                10px;

            padding:
                13px 15px;

            margin-bottom:
                22px;

            background:
                #fef2f2;

            border:
                1px solid #fecaca;

            color:
                #b91c1c;

            border-radius:
                8px;

            font-size:
                13px;
        }


        /* FORM */

        .form-grid {

            display:
                grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap:
                20px;
        }


        .form-group {

            margin-bottom:
                2px;
        }


        .form-group.full {

            grid-column:
                1 / -1;
        }


        label {

            display:
                block;

            color:
                #334155;

            font-size:
                13px;

            font-weight:
                700;

            margin-bottom:
                7px;
        }


        .required {

            color:
                #ef4444;
        }


        input,
        select,
        textarea {

            width:
                100%;

            padding:
                11px 13px;

            border:
                1px solid #cbd5e1;

            border-radius:
                8px;

            background:
                #ffffff;

            color:
                #1e293b;

            font-family:
                inherit;

            font-size:
                13px;

            outline:
                none;

            transition:
                .2s;
        }


        input::placeholder,
        textarea::placeholder {

            color:
                #94a3b8;
        }


        input:focus,
        select:focus,
        textarea:focus {

            border-color:
                #6366f1;

            box-shadow:
                0 0 0 3px
                rgba(99,102,241,.10);
        }


        textarea {

            min-height:
                110px;

            resize:
                vertical;
        }


        /* INFO */

        .info-box {

            margin-top:
                24px;

            padding:
                14px 16px;

            background:
                #f8fafc;

            border:
                1px solid #e2e8f0;

            border-radius:
                8px;

            color:
                #64748b;

            font-size:
                12px;

            line-height:
                1.6;
        }


        /* BUTTON */

        .buttons {

            display:
                flex;

            justify-content:
                flex-end;

            gap:
                10px;

            margin-top:
                28px;

            padding-top:
                22px;

            border-top:
                1px solid #e2e8f0;
        }


        .btn {

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                7px;

            padding:
                11px 17px;

            border-radius:
                8px;

            font-family:
                inherit;

            font-size:
                13px;

            font-weight:
                700;

            text-decoration:
                none;

            cursor:
                pointer;

            transition:
                .2s;
        }


        .btn-cancel {

            background:
                white;

            color:
                #475569;

            border:
                1px solid #cbd5e1;
        }


        .btn-cancel:hover {

            background:
                #f8fafc;
        }


        .btn-save {

            border:
                1px solid #4f46e5;

            background:
                #4f46e5;

            color:
                white;

            box-shadow:
                0 4px 10px
                rgba(79,70,229,.2);
        }


        .btn-save:hover {

            background:
                #4338ca;

            transform:
                translateY(-1px);

            box-shadow:
                0 6px 14px
                rgba(79,70,229,.25);
        }


        /* FOOTER */

        .footer {

            text-align:
                center;

            margin-top:
                22px;

            color:
                #94a3b8;

            font-size:
                11px;
        }


        /* RESPONSIVE */

        @media (max-width: 700px) {

            body {

                padding:
                    20px 12px;
            }


            .page-header {

                align-items:
                    flex-start;

                flex-direction:
                    column;
            }


            .back-top {

                width:
                    100%;

                text-align:
                    center;
            }


            .card-body {

                padding:
                    20px;
            }


            .form-grid {

                grid-template-columns:
                    1fr;
            }


            .form-group.full {

                grid-column:
                    auto;
            }


            .buttons {

                flex-direction:
                    column-reverse;
            }


            .btn {

                width:
                    100%;
            }

        }

    </style>

</head>


<body>


<div class="container">


    <!-- HEADER -->

    <div class="page-header">

        <div class="header-left">

            <div class="header-icon">
                ✏️
            </div>

            <div>

                <h1>
                    Edit Data Siswa
                </h1>

                <p>
                    Perbarui informasi siswa yang tersimpan
                </p>

            </div>

        </div>


        <a
            href="index.php"
            class="back-top"
        >
            ← Kembali ke Data Siswa
        </a>

    </div>


    <!-- CARD -->

    <div class="card">


        <div class="card-header">

            <h2>
                Informasi Siswa
            </h2>

            <p>
                Silakan ubah data yang diperlukan kemudian simpan perubahan.
            </p>

        </div>


        <div class="card-body">


            <?php if ($error !== ''): ?>

                <div class="error">

                    <span>⚠️</span>

                    <span>
                        <?= htmlspecialchars($error) ?>
                    </span>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                autocomplete="off"
            >


                <div class="form-grid">


                    <!-- NAMA -->

                    <div class="form-group">

                        <label>
                            Nama Lengkap
                            <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="<?= htmlspecialchars($data['name'] ?? '') ?>"
                            placeholder="Masukkan nama lengkap"
                            required
                        >

                    </div>


                    <!-- NISN -->

                    <div class="form-group">

                        <label>
                            NISN
                            <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="nisn"
                            value="<?= htmlspecialchars($data['nisn'] ?? '') ?>"
                            placeholder="Masukkan NISN"
                            maxlength="10"
                            required
                        >

                    </div>


                    <!-- TTL -->

                    <div class="form-group">

                        <label>
                            Tempat / Tanggal Lahir
                            <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="ttl"
                            value="<?= htmlspecialchars($data['ttl'] ?? '') ?>"
                            placeholder="Contoh: Sumenep, 12 Januari 2008"
                            required
                        >

                    </div>


                    <!-- GENDER -->

                    <div class="form-group">

                        <label>
                            Jenis Kelamin
                            <span class="required">*</span>
                        </label>

                        <select
                            name="gender"
                            required
                        >

                            <option value="">
                                Pilih jenis kelamin
                            </option>

                            <option
                                value="Laki-laki"
                                <?= ($data['gender'] ?? '') === 'Laki-laki'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Laki-laki
                            </option>

                            <option
                                value="Perempuan"
                                <?= ($data['gender'] ?? '') === 'Perempuan'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Perempuan
                            </option>

                        </select>

                    </div>


                    <!-- EMAIL -->

                    <div class="form-group full">

                        <label>
                            Email
                            <span class="required">*</span>
                        </label>

                        <input
                            type="email"
                            name="email"
                            value="<?= htmlspecialchars($data['email'] ?? '') ?>"
                            placeholder="contoh@email.com"
                            required
                        >

                    </div>


                    <!-- ALAMAT -->

                    <div class="form-group full">

                        <label>
                            Alamat
                            <span class="required">*</span>
                        </label>

                        <textarea
                            name="address"
                            placeholder="Masukkan alamat lengkap"
                            required
                        ><?= htmlspecialchars($data['address'] ?? '') ?></textarea>

                    </div>


                </div>


                <!-- INFO -->

                <div class="info-box">

                    <strong>Informasi:</strong>
                    Setelah data disimpan, sistem akan mengirimkan
                    notifikasi perubahan data melalui WhatsApp Fonnte.

                </div>


                <!-- BUTTON -->

                <div class="buttons">

                    <a
                        href="index.php"
                        class="btn btn-cancel"
                    >
                        Batal
                    </a>


                    <button
                        type="submit"
                        class="btn btn-save"
                    >
                        💾 Simpan Perubahan
                    </button>

                </div>


            </form>


        </div>

    </div>


    <div class="footer">

        Sistem Data Siswa • © 2026

    </div>


</div>


</body>

</html>