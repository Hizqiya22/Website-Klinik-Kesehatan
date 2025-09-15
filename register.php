<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama = $_POST['nama'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $no_hp = $_POST['no_hp'];
    $alamat = $_POST['alamat'];

    // Cek username unik
    $cek = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $cek->bind_param("s", $username);
    $cek->execute();
    $cek_result = $cek->get_result();

    if ($cek_result->num_rows > 0) {
        $error = "Username sudah digunakan!";
    } else {
        // Insert ke users
        $stmt_user = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'pasien')");
        $stmt_user->bind_param("ss", $username, $password);
        $stmt_user->execute();
        $user_id = $conn->insert_id;

        // Insert ke pasien
        $stmt_pasien = $conn->prepare("
            INSERT INTO pasien (user_id, nama, tanggal_lahir, jenis_kelamin, no_hp, alamat)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt_pasien->bind_param("isssss", $user_id, $nama, $tanggal_lahir, $jenis_kelamin, $no_hp, $alamat);
        $stmt_pasien->execute();

        header("Location: login.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrasi Pasien</title>
    <style>
        body {
            font-family: Arial;
            background: #f1f8e9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 400px;
        }
        h2 {
            color: #33691e;
            text-align: center;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            background: #33691e;
            color: white;
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 6px;
            margin-top: 10px;
        }
        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="form-box">
    <h2>Registrasi Pasien</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required />
        <input type="password" name="password" placeholder="Password" required />
        <input type="text" name="nama" placeholder="Nama Lengkap" required />
        <input type="date" name="tanggal_lahir" placeholder="Tanggal Lahir" required />
        <select name="jenis_kelamin" required>
            <option value="">-- Jenis Kelamin --</option>
            <option value="L">Laki-laki</option>
            <option value="P">Perempuan</option>
        </select>
        <input type="text" name="no_hp" placeholder="Nomor HP" required />
        <input type="text" name="alamat" placeholder="Alamat Lengkap" required />
        <button type="submit">Daftar</button>
    </form>
    <?php if (isset($error)): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>
</div>
</body>
</html>
