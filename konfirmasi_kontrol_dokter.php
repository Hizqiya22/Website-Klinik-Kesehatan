<?php
include 'koneksi.php';
include 'utils.php';
cek_login();

if ($_SESSION['role'] !== 'dokter') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil ID dokter
$q = $conn->prepare("SELECT id FROM dokter WHERE user_id = ?");
$q->bind_param("i", $user_id);
$q->execute();
$res = $q->get_result();
$dokter_data = $res->fetch_assoc();
$dokter_id = $dokter_data['id'] ?? 0;

// Proses konfirmasi (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jadwal_id'])) {
    $jadwal_id = $_POST['jadwal_id'];
    $jam_kontrol = $_POST['jam_kontrol'];

    $stmt = $conn->prepare("UPDATE jadwal_kontrol SET jam_kontrol = ?, status = 'disetujui' WHERE id = ? AND dokter_id = ?");
    $stmt->bind_param("sii", $jam_kontrol, $jadwal_id, $dokter_id);
    $stmt->execute();

    $msg = "Kontrol berhasil dikonfirmasi.";
}

// Ambil daftar kontrol menunggu
$stmt = $conn->prepare("
    SELECT jk.id, jk.tanggal_kontrol, jk.keluhan, p.nama AS nama_pasien 
    FROM jadwal_kontrol jk 
    JOIN pasien p ON jk.pasien_id = p.id 
    WHERE jk.dokter_id = ? AND jk.status = 'menunggu'
    ORDER BY jk.tanggal_kontrol ASC
");
$stmt->bind_param("i", $dokter_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Konfirmasi Jadwal Kontrol</title>
    <style>
        body { font-family: Arial; margin: 30px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #aaa; padding: 10px; text-align: left; }
        th { background-color: #00695c; color: white; }
        .form-inline input[type="time"] {
            padding: 5px;
        }
        .form-inline button {
            padding: 6px 12px;
            background: green;
            color: white;
            border: none;
        }
        .alert {
            padding: 10px;
            background: #d9ffd9;
            border: 1px solid green;
            margin-bottom: 15px;
            width: fit-content;
        }
    </style>
</head>
<body>
    <h2>Konfirmasi Jadwal Kontrol</h2>

    <?php if (!empty($msg)): ?>
        <div class="alert"><?= $msg ?></div>
    <?php endif; ?>

    <?php if ($result->num_rows === 0): ?>
        <p>Tidak ada permintaan kontrol baru.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Pasien</th>
                <th>Tanggal</th>
                <th>Keluhan</th>
                <th>Konfirmasi Jam</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama_pasien']) ?></td>
                    <td><?= $row['tanggal_kontrol'] ?></td>
                    <td><?= nl2br(htmlspecialchars($row['keluhan'])) ?></td>
                    <td>
                        <form method="POST" class="form-inline">
                            <input type="hidden" name="jadwal_id" value="<?= $row['id'] ?>">
                            <input type="time" name="jam_kontrol" required>
                            <button type="submit">Konfirmasi</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</body>
</html>
