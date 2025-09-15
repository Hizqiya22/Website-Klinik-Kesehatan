<?php
include 'koneksi.php';
include 'utils.php';
cek_login();

if ($_SESSION['role'] !== 'dokter') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['jadwal_id'])) {
    die("ID jadwal tidak ditemukan.");
}

$jadwal_id = $_GET['jadwal_id'];
$user_id = $_SESSION['user_id'];

// Ambil ID dokter
$q = $conn->prepare("SELECT id FROM dokter WHERE user_id = ?");
$q->bind_param("i", $user_id);
$q->execute();
$dokter_id = $q->get_result()->fetch_assoc()['id'] ?? 0;

// Ambil data jadwal
$stmt = $conn->prepare("
    SELECT jk.*, p.nama AS nama_pasien 
    FROM jadwal_kontrol jk 
    JOIN pasien p ON jk.pasien_id = p.id 
    WHERE jk.id = ? AND jk.dokter_id = ? AND jk.status = 'disetujui'
");
$stmt->bind_param("ii", $jadwal_id, $dokter_id);
$stmt->execute();
$jadwal = $stmt->get_result()->fetch_assoc();

if (!$jadwal) {
    die("Data tidak ditemukan atau belum dikonfirmasi.");
}

// Proses simpan diagnosa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diagnosa = $_POST['diagnosa'];
    $resep = $_POST['resep'];
    $catatan = $_POST['catatan'];
    $tanggal = date('Y-m-d');

    $stmt1 = $conn->prepare("INSERT INTO diagnosa (jadwal_id, diagnosa, resep, catatan, tanggal_diagnosa) VALUES (?, ?, ?, ?, ?)");
    $stmt1->bind_param("issss", $jadwal_id, $diagnosa, $resep, $catatan, $tanggal);
    $stmt1->execute();

    $stmt2 = $conn->prepare("UPDATE jadwal_kontrol SET status = 'selesai' WHERE id = ?");
    $stmt2->bind_param("i", $jadwal_id);
    $stmt2->execute();

    $success = "Diagnosa berhasil disimpan.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Input Diagnosa</title>
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
            background: white;
            margin: 30px auto;
            max-width: 700px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            margin-top: 0;
            color: #2f4f4f;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        textarea {
            width: 100%;
            height: 80px;
            padding: 10px;
            font-family: Arial;
            font-size: 14px;
            resize: vertical;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            margin-top: 20px;
            padding: 10px 20px;
            background: #00695c;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .alert {
            background: #d9ffd9;
            border: 1px solid green;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .info p {
            margin: 5px 0;
        }

        .btn-kembali {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 14px;
            background-color: #9e9e9e;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .btn-kembali:hover {
            background-color: #757575;
        }

        .btn-pdf {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 14px;
            background-color: #1976d2;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .btn-pdf:hover {
            background-color: #1565c0;
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
    <a href="jadwal_kontrol_dokter.php" class="btn-kembali">🔙 Kembali ke Jadwal Kontrol</a>

    <h2>Input Diagnosa Pasien</h2>

    <?php if (!empty($success)): ?>
        <div class="alert"><?= $success ?></div>
        <a href="cetak_diagnosa.php?jadwal_id=<?= $jadwal_id ?>" target="_blank" class="btn-pdf">🖨 Cetak Diagnosa (PDF)</a>
    <?php else: ?>
        <div class="info">
            <p><strong>Pasien:</strong> <?= htmlspecialchars($jadwal['nama_pasien']) ?></p>
            <p><strong>Tanggal:</strong> <?= $jadwal['tanggal_kontrol'] ?>, Jam: <?= $jadwal['jam_kontrol'] ?></p>
            <p><strong>Keluhan:</strong><br><?= nl2br(htmlspecialchars($jadwal['keluhan'])) ?></p>
        </div>

        <form method="POST">
            <label>Diagnosa:</label>
            <textarea name="diagnosa" required></textarea>

            <label>Resep:</label>
            <textarea name="resep"></textarea>

            <label>Catatan Tambahan:</label>
            <textarea name="catatan"></textarea>

            <button type="submit">💾 Simpan Diagnosa</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
