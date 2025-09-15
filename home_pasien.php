<?php
include 'koneksi.php';
include 'utils.php';
cek_login();

if ($_SESSION['role'] !== 'pasien') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home Pasien</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f0fdf6;
        }

        .navbar {
            background-color: #d6e5e0;
            padding: 10px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .navbar .left {
            display: flex;
            align-items: center;
            color: #1b3b2f;
            font-size: 20px;
            font-weight: bold;
        }

        .navbar .left img {
            height: 60px;
            margin-right: 12px;
        }

        .navbar .right a {
            color: #1b3b2f;
            text-decoration: none;
            margin-left: 20px;
            font-weight: 500;
            transition: 0.2s;
        }

        .navbar .right a:hover {
            color: #388e7b;
        }

        .container {
            padding: 40px;
        }

        h2 {
            color: #2f4f4f;
        }

        p {
            font-size: 16px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="left">
            <img src="logo-klinik.png" alt="Logo Klinik">
            Uncip Clinic | Pasien
        </div>
        <div class="right">
            <a href="home_pasien.php">Home</a>
            <a href="jadwal_kontrol_pasien.php">Daftar Jadwal Kontrol</a>
            <a href="jadwal_pasien.php">Jadwal Kontrol</a>
            <a href="diagnosa_pasien.php">Lihat Diagnosa</a>
            <a href="tagihan_pasien.php">Lihat Tagihan</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>Selamat datang, Pasien!</h2>
        <p>Silakan pilih menu dari navigasi di atas untuk mengakses layanan kontrol, diagnosa, dan tagihan.</p>
    </div>
</body>
</html>
