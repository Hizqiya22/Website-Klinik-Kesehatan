<?php
include 'koneksi.php';
include 'utils.php';
cek_login();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Klinik</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fafafa;
            padding: 40px;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: auto;
        }
        h2 {
            color: #1976d2;
        }
        ul {
            padding-left: 20px;
        }
        li {
            margin: 8px 0;
        }
        .logout {
            text-align: right;
        }
        .logout a {
            color: red;
            text-decoration: none;
            font-weight: bold;
        }
        .role {
            background: #e3f2fd;
            padding: 8px 12px;
            display: inline-block;
            border-radius: 5px;
            margin-bottom: 10px;
            color: #1976d2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logout"><a href="logout.php">Logout</a></div>
        <h2>Selamat datang di Klinik Kesehatan</h2>
        <div class="role">Login sebagai: <strong><?= $_SESSION['role'] ?></strong></div>
        <hr>

        <?php if (role('admin')): ?>
            <h3>Menu Admin</h3>
            <ul>
                <li>Manajemen Dokter</li>
                <li>Manajemen Pasien</li>
                <li>Melihat Jadwal Kontrol</li>
                <li>Input Tagihan</li>
            </ul>
        <?php endif; ?>

        <?php if (role('dokter')): ?>
            <h3>Menu Dokter</h3>
            <ul>
                <li>Lihat Jadwal Kontrol</li>
                <li>Input Diagnosa</li>
            </ul>
        <?php endif; ?>

        <?php if (role('pasien')): ?>
            <h3>Menu Pasien</h3>
            <ul>
                <li>Daftar Jadwal Kontrol</li>
                <li>Lihat Diagnosa</li>
                <li>Lihat Tagihan</li>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>
