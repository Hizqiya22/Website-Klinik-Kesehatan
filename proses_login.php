<?php
session_start();
include 'koneksi.php';

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        // Redirect sesuai role
        if ($user['role'] === 'admin') {
        header("Location: home_admin.php");
        } elseif ($user['role'] === 'dokter') {
        header("Location: home_dokter.php");
        } else {
        header("Location: home_pasien.php");
        }
        exit;
    }
}

$_SESSION['error'] = "Login gagal: Username atau password salah.";
header("Location: login.php");
exit;
