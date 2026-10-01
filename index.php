<?php

session_start();

require_once __DIR__ . "/koneksi.php";

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
| DATA SESSION
|--------------------------------------------------------------------------
*/

$namaUser = $_SESSION['nama'] ?? 'Administrator';
$username = $_SESSION['username'] ?? 'User';


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');


/*
|--------------------------------------------------------------------------
| QUERY DATA
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $query = "
        SELECT *
        FROM users
        WHERE
            name LIKE ?
            OR nisn LIKE ?
            OR email LIKE ?
            OR ttl LIKE ?
            OR gender LIKE ?
            OR address LIKE ?
        ORDER BY id DESC
    ";

    $stmt = $koneksi->prepare($query);

    $keyword = "%" . $search . "%";

    $stmt->bind_param(
        "ssssss",
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword,
        $keyword
    );

    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $query = "
        SELECT *
        FROM users
        ORDER BY id DESC
    ";

    $result = $koneksi->query($query);
}


/*
|--------------------------------------------------------------------------
| STATISTIK
|--------------------------------------------------------------------------
*/

$totalSiswa = 0;
$totalLaki = 0;
$totalPerempuan = 0;

$statQuery = $koneksi->query("
    SELECT
        COUNT(*) AS total,
        SUM(
            gender = 'MALE'
            OR gender = 'Laki-laki'
        ) AS laki,
        SUM(
            gender = 'FEMALE'
            OR gender = 'Perempuan'
        ) AS perempuan
    FROM users
");

if ($statQuery) {

    $stat = $statQuery->fetch_assoc();

    $totalSiswa =
        (int) ($stat['total'] ?? 0);

    $totalLaki =
        (int) ($stat['laki'] ?? 0);

    $totalPerempuan =
        (int) ($stat['perempuan'] ?? 0);
}


/*
|--------------------------------------------------------------------------
| NOTIFIKASI WHATSAPP
|--------------------------------------------------------------------------
*/

$waNotif = $_SESSION['wa_notif'] ?? null;
$waError = $_SESSION['wa_error'] ?? null;

unset($_SESSION['wa_notif']);
unset($_SESSION['wa_error']);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Data Siswa | ASTS</title>


    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {

            font-family:
                "Segoe UI",
                Arial,
                sans-serif;

            background:
                linear-gradient(
                    135deg,
                    #eef2ff,
                    #f8fafc,
                    #f5f3ff
                );

            color: #1e293b;

            min-height: 100vh;
        }


        /* =====================================================
           SIDEBAR
        ===================================================== */

        .sidebar {

            position: fixed;

            left: 0;
            top: 0;
            bottom: 0;

            width: 250px;

            background:
                linear-gradient(
                    180deg,
                    #312e81,
                    #4338ca,
                    #4f46e5
                );

            padding: 25px 18px;

            color: white;

            box-shadow:
                5px 0 25px
                rgba(49, 46, 129, .15);

            z-index: 10;
        }


        .brand {

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 5px 10px 28px;

            border-bottom:
                1px solid
                rgba(255,255,255,.15);

            margin-bottom: 25px;
        }


        .brand-icon {

            width: 43px;
            height: 43px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 12px;

            background:
                rgba(255,255,255,.15);

            font-size: 21px;
        }


        .brand h2 {

            font-size: 17px;

            font-weight: 700;
        }


        .brand span {

            display: block;

            font-size: 11px;

            color: #c7d2fe;

            margin-top: 2px;
        }


        .menu-title {

            font-size: 10px;

            text-transform: uppercase;

            letter-spacing: 1.5px;

            color: #c7d2fe;

            padding: 0 12px;

            margin-bottom: 10px;
        }


        .menu {

            display: flex;

            flex-direction: column;

            gap: 6px;
        }


        .menu a {

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 12px 13px;

            color: #e0e7ff;

            text-decoration: none;

            border-radius: 10px;

            font-size: 13px;

            transition: .2s;
        }


        .menu a:hover,
        .menu a.active {

            background:
                rgba(255,255,255,.16);

            color: white;

            transform: translateX(3px);
        }


        .menu-icon {

            width: 25px;

            text-align: center;

            font-size: 17px;
        }


        .sidebar-bottom {

            position: absolute;

            left: 18px;
            right: 18px;
            bottom: 20px;
        }


        .user-box {

            padding: 13px;

            border-radius: 12px;

            background:
                rgba(255,255,255,.10);

            margin-bottom: 10px;
        }


        .user-name {

            font-size: 13px;

            font-weight: 700;

            white-space: nowrap;

            overflow: hidden;

            text-overflow: ellipsis;
        }


        .user-role {

            font-size: 11px;

            color: #c7d2fe;

            margin-top: 3px;
        }


        .logout {

            width: 100%;

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding: 10px;

            border-radius: 9px;

            text-decoration: none;

            color: #fee2e2;

            background:
                rgba(239,68,68,.15);

            font-size: 12px;

            border:
                1px solid
                rgba(248,113,113,.2);

            transition: .2s;
        }


        .logout:hover {

            background:
                rgba(239,68,68,.3);
        }


        /* =====================================================
           MAIN
        ===================================================== */

        .main {

            margin-left: 250px;

            min-height: 100vh;

            padding: 30px;
        }


        .topbar {

            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 25px;

            gap: 20px;
        }


        .page-title h1 {

            font-size: 26px;

            color: #1e1b4b;

            margin-bottom: 5px;
        }


        .page-title p {

            color: #64748b;

            font-size: 13px;
        }


        .date-box {

            background: white;

            padding: 10px 15px;

            border-radius: 10px;

            border:
                1px solid #e2e8f0;

            color: #475569;

            font-size: 12px;

            box-shadow:
                0 4px 15px
                rgba(15,23,42,.05);
        }


        /* =====================================================
           ALERT
        ===================================================== */

        .alert {

            display: flex;

            align-items: center;

            gap: 12px;

            padding: 14px 17px;

            border-radius: 12px;

            margin-bottom: 20px;

            font-size: 13px;
        }


        .alert-success {

            background: #ecfdf5;

            border: 1px solid #a7f3d0;

            color: #047857;
        }


        .alert-warning {

            background: #fffbeb;

            border: 1px solid #fde68a;

            color: #b45309;
        }


        /* =====================================================
           STAT CARDS
        ===================================================== */

        .stats {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 18px;

            margin-bottom: 25px;
        }


        .stat-card {

            position: relative;

            overflow: hidden;

            background: white;

            border:
                1px solid #e2e8f0;

            border-radius: 16px;

            padding: 20px;

            box-shadow:
                0 5px 20px
                rgba(15,23,42,.05);

            transition: .2s;
        }


        .stat-card:hover {

            transform: translateY(-3px);

            box-shadow:
                0 10px 25px
                rgba(15,23,42,.09);
        }


        .stat-card::after {

            content: "";

            position: absolute;

            width: 90px;
            height: 90px;

            border-radius: 50%;

            right: -30px;
            top: -30px;

            opacity: .1;
        }


        .stat-blue::after {
            background: #4f46e5;
        }


        .stat-green::after {
            background: #10b981;
        }


        .stat-pink::after {
            background: #ec4899;
        }


        .stat-top {

            display: flex;

            justify-content: space-between;

            align-items: center;
        }


        .stat-icon {

            width: 45px;
            height: 45px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 12px;

            font-size: 20px;
        }


        .blue-icon {

            background: #eef2ff;

            color: #4f46e5;
        }


        .green-icon {

            background: #ecfdf5;

            color: #059669;
        }


        .pink-icon {

            background: #fdf2f8;

            color: #db2777;
        }


        .stat-label {

            color: #64748b;

            font-size: 12px;

            margin-top: 15px;
        }


        .stat-number {

            font-size: 27px;

            font-weight: 800;

            color: #1e293b;

            margin-top: 3px;
        }


        /* =====================================================
           CONTENT CARD
        ===================================================== */

        .content-card {

            background: white;

            border:
                1px solid #e2e8f0;

            border-radius: 16px;

            box-shadow:
                0 5px 20px
                rgba(15,23,42,.05);

            overflow: hidden;
        }


        .content-header {

            padding: 20px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            border-bottom:
                1px solid #e2e8f0;
        }


        .content-header h2 {

            color: #1e1b4b;

            font-size: 17px;
        }


        .content-header p {

            color: #64748b;

            font-size: 11px;

            margin-top: 4px;
        }


        .header-actions {

            display: flex;

            align-items: center;

            gap: 10px;
        }


        .search {

            position: relative;
        }


        .search input {

            width: 230px;

            padding:
                10px
                12px
                10px
                37px;

            border:
                1px solid #cbd5e1;

            border-radius: 9px;

            outline: none;

            font-size: 12px;

            color: #334155;

            transition: .2s;
        }


        .search input:focus {

            border-color: #6366f1;

            box-shadow:
                0 0 0 3px
                rgba(99,102,241,.1);
        }


        .search-icon {

            position: absolute;

            left: 12px;

            top: 50%;

            transform:
                translateY(-50%);

            color: #64748b;
        }


        .btn-add {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            padding: 10px 15px;

            border-radius: 9px;

            color: white;

            background:
                linear-gradient(
                    135deg,
                    #4f46e5,
                    #7c3aed
                );

            text-decoration: none;

            font-size: 12px;

            font-weight: 700;

            box-shadow:
                0 4px 12px
                rgba(79,70,229,.25);

            transition: .2s;
        }


        .btn-add:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 7px 17px
                rgba(79,70,229,.3);
        }


        /* =====================================================
           TABLE
        ===================================================== */

        .table-wrapper {

            overflow-x: auto;
        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 900px;
        }


        thead {

            background: #f8fafc;
        }


        th {

            text-align: left;

            padding: 13px 16px;

            color: #64748b;

            font-size: 11px;

            text-transform: uppercase;

            letter-spacing: .4px;

            font-weight: 700;

            border-bottom:
                1px solid #e2e8f0;
        }


        td {

            padding: 14px 16px;

            font-size: 12px;

            color: #475569;

            border-bottom:
                1px solid #f1f5f9;

            vertical-align: middle;
        }


        tbody tr {

            transition: .15s;
        }


        tbody tr:hover {

            background: #f8fafc;
        }


        .number {

            width: 50px;

            color: #94a3b8;

            font-weight: 600;
        }


        .student {

            display: flex;

            align-items: center;

            gap: 10px;
        }


        .avatar {

            width: 37px;
            height: 37px;

            border-radius: 10px;

            display: flex;

            align-items: center;
            justify-content: center;

            color: white;

            font-size: 13px;

            font-weight: 700;

            background:
                linear-gradient(
                    135deg,
                    #4f46e5,
                    #9333ea
                );
        }


        .student-name {

            color: #1e293b;

            font-weight: 700;

            font-size: 12px;
        }


        .student-id {

            color: #94a3b8;

            font-size: 10px;

            margin-top: 2px;
        }


        .nisn {

            font-family: monospace;

            color: #475569;
        }


        .badge {

            display: inline-flex;

            padding: 5px 9px;

            border-radius: 20px;

            font-size: 10px;

            font-weight: 700;
        }


        .male {

            background: #eff6ff;

            color: #2563eb;
        }


        .female {

            background: #fdf2f8;

            color: #db2777;
        }


        .other {

            background: #f1f5f9;

            color: #64748b;
        }


        .action {

            display: flex;

            gap: 6px;
        }


        .btn-action {

            width: 32px;
            height: 32px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 8px;

            text-decoration: none;

            font-size: 13px;

            transition: .2s;
        }


        .btn-edit {

            color: #4f46e5;

            background: #eef2ff;
        }


        .btn-edit:hover {

            background: #e0e7ff;

            transform: translateY(-2px);
        }


        .btn-delete {

            color: #dc2626;

            background: #fef2f2;
        }


        .btn-delete:hover {

            background: #fee2e2;

            transform: translateY(-2px);
        }


        .empty {

            text-align: center;

            padding: 50px 20px;

            color: #94a3b8;
        }


        .empty-icon {

            font-size: 40px;

            margin-bottom: 10px;
        }


        .empty h3 {

            color: #475569;

            font-size: 15px;

            margin-bottom: 5px;
        }


        .empty p {

            font-size: 12px;
        }


        /* =====================================================
           FOOTER
        ===================================================== */

        .footer {

            text-align: center;

            padding: 25px 0 5px;

            color: #94a3b8;

            font-size: 11px;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 900px) {

            .sidebar {

                width: 70px;

                padding: 20px 10px;
            }


            .brand {

                justify-content: center;

                padding: 5px 0 20px;
            }


            .brand-text,
            .menu-title,
            .menu a span,
            .user-box {

                display: none;
            }


            .brand-icon {

                width: 43px;
            }


            .menu a {

                justify-content: center;

                padding: 12px;
            }


            .sidebar-bottom {

                left: 10px;
                right: 10px;
            }


            .logout {

                font-size: 0;

                height: 40px;
            }


            .logout:first-letter {

                font-size: 15px;
            }


            .main {

                margin-left: 70px;

                padding: 20px;
            }


            .stats {

                grid-template-columns:
                    1fr 1fr;
            }
        }


        @media (max-width: 650px) {

            .main {

                padding: 15px;
            }


            .topbar {

                align-items: flex-start;

                flex-direction: column;
            }


            .date-box {

                display: none;
            }


            .stats {

                grid-template-columns: 1fr;
            }


            .content-header {

                align-items: stretch;

                flex-direction: column;
            }


            .header-actions {

                flex-direction: column;

                align-items: stretch;
            }


            .search input {

                width: 100%;
            }


            .btn-add {

                justify-content: center;
            }
        }

    </style>

</head>


<body>


<!-- =========================================================
     SIDEBAR
========================================================= -->

<aside class="sidebar">


    <div class="brand">

        <div class="brand-icon">
            📚
        </div>

        <div class="brand-text">

            <h2>ASTS Panel</h2>

            <span>Sistem Data Siswa</span>

        </div>

    </div>


    <div class="menu-title">
        Menu Utama
    </div>


    <nav class="menu">

        <a
            href="index.php"
            class="active"
        >

            <div class="menu-icon">
                🏠
            </div>

            <span>
                Dashboard
            </span>

        </a>


        <a href="data_json.php" class="menu-item active">
            <span>📄</span>
            <span>Data JSON</span>
        </a>


        <a href="tambah.php">

            <div class="menu-icon">
                ➕
            </div>

            <span>
                Tambah Siswa
            </span>

        </a>

    </nav>


    <div class="sidebar-bottom">


        <div class="user-box">

            <div class="user-name">

                👤
                <?= htmlspecialchars($namaUser) ?>

            </div>

            <div class="user-role">

                Administrator

            </div>

        </div>


        <a
            href="logout.php"
            class="logout"
            onclick="return confirm('Apakah Anda yakin ingin logout?')"
        >

            🚪

            <span>
                Logout
            </span>

        </a>


    </div>

</aside>


<!-- =========================================================
     MAIN
========================================================= -->

<main class="main">


    <!-- TOPBAR -->

    <div class="topbar">

        <div class="page-title">

            <h1>
                Dashboard
            </h1>

            <p>
                Kelola dan pantau data siswa dengan mudah.
            </p>

        </div>


        <div class="date-box">

            📅

            <?= date('d F Y') ?>

        </div>

    </div>


    <!-- =====================================================
         NOTIFICATION
    ===================================================== -->

    <?php if (
        is_array($waNotif) &&
        isset($waNotif['message'])
    ): ?>


        <?php if (
            isset($waNotif['status']) &&
            $waNotif['status'] === true
        ): ?>

            <div class="alert alert-success">

                <span>
                    ✅
                </span>

                <div>

                    <?= htmlspecialchars(
                        $waNotif['message']
                    ) ?>

                </div>

            </div>

        <?php else: ?>

            <div class="alert alert-warning">

                <span>
                    ⚠️
                </span>

                <div>

                    <?= htmlspecialchars(
                        $waNotif['message']
                    ) ?>

                    <?php if ($waError): ?>

                        <div style="
                            margin-top:5px;
                            font-size:11px;
                        ">

                            <?= htmlspecialchars(
                                is_string($waError)
                                    ? $waError
                                    : json_encode($waError)
                            ) ?>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        <?php endif; ?>


    <?php endif; ?>


    <!-- =====================================================
         STATISTIK
    ===================================================== -->

    <section class="stats">


        <div class="stat-card stat-blue">

            <div class="stat-top">

                <div>

                    <div class="stat-label">
                        Total Siswa
                    </div>

                    <div class="stat-number">
                        <?= number_format($totalSiswa) ?>
                    </div>

                </div>


                <div class="stat-icon blue-icon">
                    👨‍🎓
                </div>

            </div>

        </div>


        <div class="stat-card stat-green">

            <div class="stat-top">

                <div>

                    <div class="stat-label">
                        Siswa Laki-laki
                    </div>

                    <div class="stat-number">
                        <?= number_format($totalLaki) ?>
                    </div>

                </div>


                <div class="stat-icon green-icon">
                    👨
                </div>

            </div>

        </div>


        <div class="stat-card stat-pink">

            <div class="stat-top">

                <div>

                    <div class="stat-label">
                        Siswa Perempuan
                    </div>

                    <div class="stat-number">
                        <?= number_format($totalPerempuan) ?>
                    </div>

                </div>


                <div class="stat-icon pink-icon">
                    👩
                </div>

            </div>

        </div>


    </section>


    <!-- =====================================================
         DATA SISWA
    ===================================================== -->

    <section class="content-card">


        <div class="content-header">


            <div>

                <h2>
                    Data Siswa
                </h2>

                <p>
                    Daftar seluruh data siswa yang tersimpan
                </p>

            </div>


            <div class="header-actions">


                <form
                    method="GET"
                    class="search"
                >

                    <span class="search-icon">
                        🔎
                    </span>

                    <input
                        type="text"
                        name="search"
                        value="<?= htmlspecialchars($search) ?>"
                        placeholder="Cari siswa..."
                    >

                </form>


                <a
                    href="tambah.php"
                    class="btn-add"
                >

                    ➕

                    Tambah Data

                </a>


            </div>


        </div>


        <div class="table-wrapper">


            <table>


                <thead>

                    <tr>

                        <th>
                            No
                        </th>

                        <th>
                            Siswa
                        </th>

                        <th>
                            NISN
                        </th>

                        <th>
                            TTL
                        </th>

                        <th>
                            Gender
                        </th>

                        <th>
                            Email
                        </th>

                        <th>
                            Alamat
                        </th>

                        <th>
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php if (
                    $result &&
                    $result->num_rows > 0
                ): ?>


                    <?php
                    $no = 1;

                    while (
                        $row =
                        $result->fetch_assoc()
                    ):

                        $nama =
                            $row['name'] ?? '';

                        $initial =
                            strtoupper(
                                substr(
                                    $nama,
                                    0,
                                    1
                                )
                            );

                        $gender =
                            strtoupper(
                                trim(
                                    $row['gender'] ?? ''
                                )
                            );


                        if (
                            $gender === 'MALE' ||
                            $gender === 'LAKI-LAKI'
                        ) {

                            $genderClass = 'male';

                            $genderText = 'Laki-laki';

                        } elseif (
                            $gender === 'FEMALE' ||
                            $gender === 'PEREMPUAN'
                        ) {

                            $genderClass = 'female';

                            $genderText = 'Perempuan';

                        } else {

                            $genderClass = 'other';

                            $genderText =
                                $row['gender'] ?? '-';
                        }

                    ?>


                        <tr>


                            <td class="number">

                                <?= $no++ ?>

                            </td>


                            <td>

                                <div class="student">


                                    <div class="avatar">

                                        <?= htmlspecialchars(
                                            $initial
                                        ) ?>

                                    </div>


                                    <div>

                                        <div class="student-name">

                                            <?= htmlspecialchars(
                                                $nama
                                            ) ?>

                                        </div>

                                        <div class="student-id">

                                            ID:
                                            <?= htmlspecialchars(
                                                $row['id']
                                            ) ?>

                                        </div>

                                    </div>


                                </div>

                            </td>


                            <td class="nisn">

                                <?= htmlspecialchars(
                                    $row['nisn'] ?? '-'
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $row['ttl'] ?? '-'
                                ) ?>

                            </td>


                            <td>

                                <span
                                    class="badge <?= $genderClass ?>"
                                >

                                    <?= htmlspecialchars(
                                        $genderText
                                    ) ?>

                                </span>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $row['email'] ?? '-'
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $row['address'] ?? '-'
                                ) ?>

                            </td>


                            <td>


                                <div class="action">


                                    <a
                                        href="edit.php?id=<?= (int)$row['id'] ?>"
                                        class="btn-action btn-edit"
                                        title="Edit"
                                    >

                                        ✏️

                                    </a>


                                    <a
                                        href="hapus.php?id=<?= (int)$row['id'] ?>"
                                        class="btn-action btn-delete"
                                        title="Hapus"
                                        onclick="
                                            return confirm(
                                                'Apakah Anda yakin ingin menghapus data <?= htmlspecialchars(
                                                    $nama,
                                                    ENT_QUOTES
                                                ) ?>?'
                                            );
                                        "
                                    >

                                        🗑️

                                    </a>


                                </div>


                            </td>


                        </tr>


                    <?php endwhile; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="8"
                            class="empty"
                        >

                            <div class="empty-icon">
                                📭
                            </div>

                            <h3>
                                Data siswa tidak ditemukan
                            </h3>

                            <p>

                                <?php if ($search !== ''): ?>

                                    Tidak ada data yang cocok
                                    dengan pencarian
                                    "<?= htmlspecialchars($search) ?>".

                                <?php else: ?>

                                    Belum ada data siswa
                                    yang tersimpan.

                                <?php endif; ?>

                            </p>

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>


            </table>


        </div>


    </section>


    <div class="footer">

        © 2026 ASTS Panel —
        Sistem Data Siswa

    </div>


</main>


</body>

</html>