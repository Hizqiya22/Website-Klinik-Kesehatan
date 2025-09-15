<?php
include 'koneksi.php';
include 'utils.php';
cek_login();

if ($_SESSION['role'] !== 'pasien') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$q_pasien = $conn->prepare("SELECT id FROM pasien WHERE user_id = ?");
$q_pasien->bind_param("i", $user_id);
$q_pasien->execute();
$res = $q_pasien->get_result();
$data_pasien = $res->fetch_assoc();

if (!$data_pasien) {
    die("Data pasien tidak ditemukan.");
}
$id_pasien = $data_pasien['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dokter_id = $_POST['dokter_id'];
    $tanggal = $_POST['tanggal_kontrol'];
    $keluhan = $_POST['keluhan'];

    $stmt = $conn->prepare("INSERT INTO jadwal_kontrol (pasien_id, dokter_id, tanggal_kontrol, keluhan, status) VALUES (?, ?, ?, ?, 'menunggu')");
    $stmt->bind_param("iiss", $id_pasien, $dokter_id, $tanggal, $keluhan);

    if ($stmt->execute()) {
        $success = "Pendaftaran kontrol berhasil!";
    } else {
        $error = "Gagal daftar: " . $conn->error;
    }
}

$dokter_result = $conn->query("
    SELECT dokter.id, dokter.nama, dokter.spesialis
    FROM dokter
    JOIN users ON dokter.user_id = users.id
    WHERE users.role = 'dokter'
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Jadwal Kontrol</title>
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
            margin-bottom: 20px;
        }

        form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: 500;
        }

        input, select, textarea {
            padding: 10px;
            width: 100%;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        button {
            margin-top: 25px;
            padding: 12px;
            width: 100%;
            background: #388e3c;
            color: white;
            border: none;
            font-weight: bold;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #2e7d32;
        }

        .alert {
            margin-top: 20px;
            padding: 10px 15px;
            width: fit-content;
            border-radius: 6px;
        }

        .success { background: #d9ffd9; border: 1px solid green; }
        .error { background: #ffd9d9; border: 1px solid red; }
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
        <h2>Form Daftar Jadwal Kontrol</h2>

        <?php if (!empty($success)): ?>
            <div class="alert success">✅ <?= $success ?></div>
        <?php elseif (!empty($error)): ?>
            <div class="alert error">⚠️ <?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Pilih Dokter:</label>
            <select name="dokter_id" required>
                <option value="">-- Pilih Dokter --</option>
                <?php while ($dokter = $dokter_result->fetch_assoc()): ?>
                    <option value="<?= $dokter['id'] ?>">
                        <?= $dokter['nama'] ?> (<?= $dokter['spesialis'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Tanggal Kontrol:</label>
            <input type="date" name="tanggal_kontrol" min="<?= date('Y-m-d') ?>" required>

            <label>Keluhan / Gejala:</label>
            <textarea name="keluhan" rows="4" required placeholder="Contoh: Pusing, batuk, mual..."></textarea>

            <button type="submit">📝 Daftar Kontrol</button>
        </form>
    </div>
</body>
</html>
