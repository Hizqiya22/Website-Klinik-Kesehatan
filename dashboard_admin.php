<?php
include 'koneksi.php';
include 'utils.php';
cek_login();
if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f5f5f5; }
        .navbar {
            background-color: #0d47a1;
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
        <div class="left">Klinik | Admin</div>
        <div class="right">
            <a href="home_admin.php">Home</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <h2>Selamat datang, Admin!</h2>
        <p>Silakan pilih menu dari AppBar di atas.</p>
    </div>
</body>
</html>
