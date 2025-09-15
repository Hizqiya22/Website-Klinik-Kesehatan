<?php
include 'koneksi.php';
include 'utils.php';
cek_login();

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$keyword = $_GET['keyword'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where = [];
if (!empty($keyword)) {
    $key = $conn->real_escape_string($keyword);
    $where[] = "(p.nama LIKE '%$key%' OR d.nama LIKE '%$key%')";
}
if (!empty($status_filter)) {
    $status_clean = $conn->real_escape_string($status_filter);
    $where[] = "jk.status = '$status_clean'";
}
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

$query = "
    SELECT jk.*, 
           p.nama AS nama_pasien, p.tanggal_lahir, p.jenis_kelamin, p.no_hp, p.alamat,
           d.nama AS nama_dokter, d.spesialis
    FROM jadwal_kontrol jk
    JOIN pasien p ON jk.pasien_id = p.id
    JOIN dokter d ON jk.dokter_id = d.id
    $where_sql
    ORDER BY jk.tanggal_kontrol DESC, jk.jam_kontrol DESC
";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Jadwal Kontrol - Admin</title>
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
            padding: 30px;
        }

        h2 {
            margin-bottom: 10px;
        }

        .filter-form {
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-form input, .filter-form select {
            padding: 8px;
        }

        .filter-form button {
            padding: 8px 16px;
            background: #1b3b2f;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #aaa;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #1b3b2f;
            color: white;
        }

        .status-menunggu { color: orange; font-weight: bold; }
        .status-disetujui { color: green; font-weight: bold; }
        .status-selesai { color: gray; font-weight: bold; }
        .status-batal { color: red; font-weight: bold; }

        a.pdf-btn {
            display: inline-block;
            margin-top: 8px;
            background: #1976d2;
            color: white;
            padding: 4px 8px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="left">
        <img src="logo-klinik.png" alt="Logo Klinik">
        Uncip Clinic | Admin
    </div>
    <div class="right">
        <a href="home_admin.php">Home</a>
        <a href="manajemen_dokter_admin.php">Manajemen Dokter</a>
        <a href="manajemen_pasien_admin.php">Manajemen Pasien</a>
        <a href="jadwal_kontrol_admin.php">Jadwal Kontrol</a>
        <a href="tagihan_admin.php">Tagihan</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>Aktivitas Jadwal Kontrol</h2>

    <form class="filter-form" method="GET">
        <input type="text" name="keyword" placeholder="Cari pasien atau dokter" value="<?= htmlspecialchars($keyword) ?>">
        <select name="status">
            <option value="">-- Semua Status --</option>
            <option value="menunggu" <?= $status_filter === 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
            <option value="disetujui" <?= $status_filter === 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
            <option value="selesai" <?= $status_filter === 'selesai' ? 'selected' : '' ?>>Selesai</option>
        </select>
        <button type="submit">🔍 Cari</button>
    </form>

    <?php if ($result->num_rows === 0): ?>
        <p>Tidak ada data jadwal kontrol ditemukan.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Pasien</th>
                <th>Identitas</th>
                <th>Dokter</th>
                <th>Spesialis</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>Keluhan</th>
                <th>Status</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama_pasien']) ?></td>
                    <td>
                        Tgl Lahir: <?= htmlspecialchars($row['tanggal_lahir']) ?><br>
                        JK: <?= htmlspecialchars($row['jenis_kelamin']) ?><br>
                        HP: <?= htmlspecialchars($row['no_hp']) ?><br>
                        Alamat: <?= htmlspecialchars($row['alamat']) ?>
                    </td>
                    <td><?= htmlspecialchars($row['nama_dokter']) ?></td>
                    <td><?= htmlspecialchars($row['spesialis']) ?></td>
                    <td><?= $row['tanggal_kontrol'] ?></td>
                    <td><?= $row['jam_kontrol'] ?? '-' ?></td>
                    <td><?= nl2br(htmlspecialchars($row['keluhan'])) ?></td>
                    <td class="status-<?= strtolower($row['status']) ?>">
                        <?= ucfirst($row['status']) ?>
                        <?php
                        if ($row['status'] === 'selesai') {
                            $cek_diag = $conn->prepare("SELECT id FROM diagnosa WHERE jadwal_id = ?");
                            $cek_diag->bind_param("i", $row['id']);
                            $cek_diag->execute();
                            $hasil = $cek_diag->get_result();
                            if ($hasil->num_rows > 0) {
                                echo '<br><a class="pdf-btn" target="_blank" href="cetak_diagnosa.php?jadwal_id=' . $row['id'] . '">📄 Cetak Diagnosa</a>';
                            }
                        }
                        ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
