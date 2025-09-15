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
$data_pasien = $res->fetch_assoc();
$pasien_id = $data_pasien['id'] ?? 0;

// Ambil semua jadwal pasien ini
$stmt = $conn->prepare("
    SELECT jk.*, d.nama AS nama_dokter 
    FROM jadwal_kontrol jk
    JOIN dokter d ON jk.dokter_id = d.id
    WHERE jk.pasien_id = ?
    ORDER BY jk.tanggal_kontrol DESC
");
$stmt->bind_param("i", $pasien_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Jadwal Kontrol Saya</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        th, td {
            padding: 12px 15px;
            border: 1px solid #ccc;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #1b3b2f;
            color: white;
        }

        .status-menunggu {
            color: orange;
            font-weight: bold;
        }

        .status-disetujui {
            color: green;
            font-weight: bold;
        }

        .status-selesai {
            color: gray;
            font-weight: bold;
        }

        p {
            font-size: 15px;
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
    <h2>Jadwal Kontrol Anda</h2>

    <?php if ($result->num_rows === 0): ?>
        <p>Anda belum memiliki jadwal kontrol.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Dokter</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>Keluhan</th>
                <th>Status</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama_dokter']) ?></td>
                    <td><?= $row['tanggal_kontrol'] ?></td>
                    <td><?= $row['jam_kontrol'] ?: '-' ?></td>
                    <td><?= nl2br(htmlspecialchars($row['keluhan'])) ?></td>
                    <td class="status-<?= strtolower($row['status']) ?>">
                        <?= ucfirst($row['status']) ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
