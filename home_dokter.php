<?php 
include 'koneksi.php';
include 'utils.php';
cek_login();
if ($_SESSION['role'] !== 'dokter') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home Dokter</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f0fdf6;
        }

        .navbar {
            background-color: #d6e5e0; /* Warna dari background logo */
            padding: 10px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
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
    </style>
</head>
<body>
    <div class="navbar">
        <div class="left">
            <img src="logo-klinik.png" alt="Logo Klinik">
            Uncip Clinic | Dokter
        </div>
        <div class="right">
            <a href="home_dokter.php">Home</a>
            <a href="jadwal_kontrol_dokter.php">Jadwal Kontrol</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>Selamat Datang, Dokter!</h2>
        <p>Silakan pilih menu dari AppBar di atas untuk mengelola jadwal dan diagnosa pasien.</p>
    </div>
</body>
</html>
