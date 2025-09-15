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
    <title>Dashboard Dokter</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f5f5f5; }
        .navbar {
            background-color: #00695c;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar .left {
            font-size: 20px;
            color: white;
            font-weight: bold;
        }
        .navbar .right a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-weight: bold;
        }
        .container {
            padding: 30px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="left">Klinik | Dokter</div>
        <div class="right">
            <a href="home_dokter.php">Home</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>Selamat datang, Dokter!</h2>
        <p>Silakan pilih menu dari AppBar di atas.</p>
    </div>
</body>
</html>
