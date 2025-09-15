<?php
include 'koneksi.php';
include 'utils.php';
cek_login();

if ($_SESSION['role'] !== 'dokter') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$q = $conn->prepare("SELECT id FROM dokter WHERE user_id = ?");
$q->bind_param("i", $user_id);
$q->execute();
$res = $q->get_result();
$dokter_data = $res->fetch_assoc();
$dokter_id = $dokter_data['id'] ?? 0;

// Proses konfirmasi jadwal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['konfirmasi'])) {
    $jadwal_id = $_POST['jadwal_id'];
    $jam_kontrol = $_POST['jam_kontrol'];

    $stmt = $conn->prepare("UPDATE jadwal_kontrol SET jam_kontrol = ?, status = 'disetujui' WHERE id = ? AND dokter_id = ?");
    $stmt->bind_param("sii", $jam_kontrol, $jadwal_id, $dokter_id);
    $stmt->execute();
    $success = "Jadwal berhasil dikonfirmasi.";
}

// Filter pencarian
$keyword = $_GET['keyword'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';

$query = "
    SELECT jk.*, p.nama AS nama_pasien 
    FROM jadwal_kontrol jk 
    JOIN pasien p ON jk.pasien_id = p.id 
    WHERE jk.dokter_id = ?
";
$param_types = "i";
$params = [$dokter_id];

if (!empty($keyword)) {
    $query .= " AND p.nama LIKE ?";
    $param_types .= "s";
    $params[] = "%$keyword%";
}

if (!empty($tanggal)) {
    $query .= " AND jk.tanggal_kontrol = ?";
    $param_types .= "s";
    $params[] = $tanggal;
}

$query .= " ORDER BY jk.tanggal_kontrol ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Jadwal Kontrol Dokter</title>
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
        }

        .navbar .right a:hover {
            color: #388e7b;
        }

        .container {
            padding: 40px;
        }

        h2 {
            color: #2f4f4f;
        }

        .filter-form {
            margin-bottom: 25px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }

        .filter-form input {
            padding: 8px;
            width: 200px;
        }

        .filter-form button {
            padding: 8px 16px;
            background: #1b5e20;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background: #388e7b;
            color: white;
        }

        .alert {
            background: #d9ffd9;
            border: 1px solid green;
            padding: 10px;
            margin-bottom: 15px;
            width: fit-content;
        }

        form.inline {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        input[type="time"] {
            padding: 5px;
        }

        button {
            padding: 6px 12px;
            background: #2e7d32;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        a.diagnosa-btn {
            color: #1976d2;
            text-decoration: none;
            font-weight: bold;
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
    <h2>Jadwal Kontrol Pasien</h2>

    <?php if (!empty($success)): ?>
        <div class="alert"><?= $success ?></div>
    <?php endif; ?>

    <form class="filter-form" method="GET">
        <input type="text" name="keyword" placeholder="Cari nama pasien" value="<?= htmlspecialchars($keyword) ?>">
        <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
        <button type="submit">🔍 Filter</button>
        <a href="jadwal_kontrol_dokter.php" style="text-decoration:none; font-weight:bold; color:#1b3b2f;">Reset</a>
    </form>

    <?php if ($result->num_rows === 0): ?>
        <p>Tidak ada jadwal yang ditemukan.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Pasien</th>
                <th>Tanggal / Jam</th>
                <th>Keluhan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama_pasien']) ?></td>
                    <td><?= $row['tanggal_kontrol'] ?><?= $row['jam_kontrol'] ? " / " . $row['jam_kontrol'] : "" ?></td>
                    <td><?= nl2br(htmlspecialchars($row['keluhan'])) ?></td>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td>
                        <?php if ($row['status'] === 'menunggu'): ?>
                            <form method="POST" class="inline">
                                <input type="hidden" name="jadwal_id" value="<?= $row['id'] ?>">
                                <input type="time" name="jam_kontrol" required>
                                <button type="submit" name="konfirmasi">Konfirmasi</button>
                            </form>
                        <?php elseif ($row['status'] === 'disetujui'): ?>
                            <a href="input_diagnosa_dokter.php?jadwal_id=<?= $row['id'] ?>" class="diagnosa-btn">✍️ Input Diagnosa</a>
                        <?php elseif ($row['status'] === 'selesai'): ?>
                            ✅ Selesai<br>
                            <?php
                            $cek_diag = $conn->prepare("SELECT id FROM diagnosa WHERE jadwal_id = ?");
                            $cek_diag->bind_param("i", $row['id']);
                            $cek_diag->execute();
                            $hasil_diag = $cek_diag->get_result();
                            if ($hasil_diag->num_rows > 0): ?>
                                <a href="cetak_diagnosa.php?jadwal_id=<?= $row['id'] ?>" target="_blank" class="diagnosa-btn">📄 Cetak Diagnosa</a>
                            <?php endif; ?>
                        <?php else: ?>
                            ❓
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
