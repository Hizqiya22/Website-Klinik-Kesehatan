<?php
include 'koneksi.php';

$users = [
    ['admin', 'admin123', 'admin'],
    ['dokter', 'dokter123', 'dokter'],
];

foreach ($users as $u) {
    $username = $u[0];
    $password = password_hash($u[1], PASSWORD_DEFAULT);
    $role     = $u[2];

    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role);
    $stmt->execute();
}

echo "User admin dan dokter berhasil ditambahkan.";
