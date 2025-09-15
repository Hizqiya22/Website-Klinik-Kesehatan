<?php
include 'koneksi.php';
include 'utils.php';
cek_login();

if ($_SESSION['role'] !== 'pasien') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil ID pasien
$q = $conn->prepare("SELECT id FROM pasien WHERE user_id = ?");
$q->bind_param("i", $user_id);
$q->execute();
$res = $q->get_result();
$pasien = $res->fetch_assoc();
$pasien_id = $pasien['id'] ?? 0;

// Ambil semua diagnosa
$stmt = $conn->prepare("
    SELECT jk.tanggal_kontrol, jk.jam_kontrol, d.nama AS nama_dokter,
           dg.diagnosa, dg.resep, dg.catatan, dg.tanggal_diagnosa
    FROM jadwal_kontrol jk
    JOIN diagnosa dg ON jk.id = dg.jadwal_id
    JOIN dokter d ON jk.dokter_id = d.id
    WHERE jk.pasien_id = ? AND jk.status = 'selesai'
    ORDER BY jk.tanggal_kontrol DESC
");
$stmt->bind_param("i", $pasien_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hasil Diagnosa</title>
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

        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            border-left: 5px solid #1b3b2f;
        }

        .card h3 {
            margin-top: 0;
            color: #2b4c40;
        }

        .meta {
            font-size: 0.9em;
            color: #555;
            margin-bottom: 12px;
        }

        .section {
            margin-bottom: 10px;
        }

        .section strong {
            color: #1b3b2f;
        }

        p {
            color: #444;
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
    <h2>Hasil Diagnosa Anda</h2>

    <?php if ($result->num_rows === 0): ?>
        <p>Tidak ada hasil diagnosa yang tersedia saat ini.</p>
    <?php else: ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="card">
                <h3>dr. <?= htmlspecialchars($row['nama_dokter']) ?></h3>
                <div class="meta">
                    Tanggal Kontrol: <?= $row['tanggal_kontrol'] ?>, Jam: <?= $row['jam_kontrol'] ?><br>
                    Diagnosa Ditulis: <?= $row['tanggal_diagnosa'] ?>
                </div>
                <div class="section"><strong>Diagnosa:</strong><br><?= nl2br(htmlspecialchars($row['diagnosa'])) ?></div>
                <div class="section"><strong>Resep:</strong><br><?= nl2br(htmlspecialchars($row['resep'])) ?></div>
                <div class="section"><strong>Catatan Tambahan:</strong><br><?= nl2br(htmlspecialchars($row['catatan'])) ?></div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

</body>
</html>
