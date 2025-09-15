<?php
include 'koneksi.php';
include 'utils.php';
cek_login();

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Pencarian dan filter
$status = $_GET['status'] ?? '';
$metode = $_GET['metode'] ?? '';
$cari = $_GET['cari'] ?? '';

$where = "1=1";
if ($status !== '') $where .= " AND t.status = '$status'";
if ($metode !== '') $where .= " AND t.metode_pembayaran = '$metode'";
if ($cari !== '') $where .= " AND p.nama LIKE '%$cari%'";

// Tambah tagihan baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['jadwal_id'])) {
    $jadwal_id = $_POST['jadwal_id'];
    $biaya_kontrol = $_POST['biaya_kontrol'];
    $biaya_administrasi = $_POST['biaya_administrasi'];
    $biaya_tambahan = $_POST['biaya_tambahan'];

    $cek = $conn->prepare("SELECT id FROM tagihan WHERE jadwal_id = ?");
    $cek->bind_param("i", $jadwal_id);
    $cek->execute();
    $cek_result = $cek->get_result();
    if ($cek_result->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO tagihan (jadwal_id, biaya_kontrol, biaya_administrasi, biaya_tambahan) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $jadwal_id, $biaya_kontrol, $biaya_administrasi, $biaya_tambahan);
        $stmt->execute();
        $msg = "Tagihan berhasil ditambahkan.";
    } else {
        $msg = "Tagihan untuk jadwal ini sudah ada.";
    }
}

// Konfirmasi pembayaran
if (isset($_GET['konfirmasi_id'])) {
    $id = $_GET['konfirmasi_id'];
    $stmt = $conn->prepare("UPDATE tagihan SET status = 'sudah dibayar' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $msg = "Pembayaran dikonfirmasi.";
}

// Ambil jadwal untuk input tagihan
$jadwal_result = $conn->query("
    SELECT jk.id, p.nama AS nama_pasien, d.nama AS nama_dokter, jk.tanggal_kontrol
    FROM jadwal_kontrol jk
    JOIN pasien p ON jk.pasien_id = p.id
    JOIN dokter d ON jk.dokter_id = d.id
    LEFT JOIN tagihan t ON jk.id = t.jadwal_id
    WHERE jk.status = 'selesai' AND t.id IS NULL
");
$jumlah_belum_ditagih = $jadwal_result->num_rows;

// Ambil semua tagihan
$tagihan_result = $conn->query("
    SELECT t.*, p.nama AS nama_pasien, d.nama AS nama_dokter, jk.tanggal_kontrol
    FROM tagihan t
    JOIN jadwal_kontrol jk ON t.jadwal_id = jk.id
    JOIN pasien p ON jk.pasien_id = p.id
    JOIN dokter d ON jk.dokter_id = d.id
    WHERE $where
    ORDER BY t.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manajemen Tagihan</title>
    <style>
        body { font-family: Arial; margin: 0; background: #f5f5f5; }

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

        .container { padding: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { padding: 10px; border: 1px solid #aaa; vertical-align: top; }
        th { background: #424242; color: white; }
        form.inline { display: flex; gap: 10px; align-items: center; margin-bottom: 20px; }
        input[type=number], select, input[type=text] { padding: 5px; }
        .msg { background: #d9ffd9; border: 1px solid green; padding: 10px; margin-bottom: 20px; width: fit-content; }
        .status-belum-dibayar { color: red; font-weight: bold; }
        .status-menunggu-konfirmasi { color: orange; font-weight: bold; }
        .status-sudah-dibayar { color: green; font-weight: bold; }
        .filter-bar { margin-top: 20px; background: white; padding: 15px; border: 1px solid #ccc; }
        .badge {
            background: red;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 14px;
            margin-left: 10px;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="left">
        <img src="logo-klinik.png" alt="Logo Klinik" style="height: 40px; margin-right: 12px;">
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
    <?php if (!empty($msg)): ?>
        <div class="msg"><?= $msg ?></div>
    <?php endif; ?>

    <h3>
        Input Tagihan Baru
        <?php if ($jumlah_belum_ditagih > 0): ?>
            <span class="badge"><?= $jumlah_belum_ditagih ?></span>
        <?php endif; ?>
    </h3>

    <?php if ($jadwal_result->num_rows === 0): ?>
        <p><em>Tidak ada kontrol selesai yang belum ditagih.</em></p>
    <?php else: ?>
        <form method="POST" class="inline">
            <select name="jadwal_id" required>
                <option value="">-- Pilih Jadwal --</option>
                <?php while ($row = $jadwal_result->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>">
                        <?= $row['nama_pasien'] ?> (<?= $row['nama_dokter'] ?> - <?= $row['tanggal_kontrol'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>
            <input type="number" name="biaya_kontrol" placeholder="Kontrol" required>
            <input type="number" name="biaya_administrasi" placeholder="Administrasi" required>
            <input type="number" name="biaya_tambahan" placeholder="Tambahan" value="0">
            <button type="submit">Tambah</button>
        </form>
    <?php endif; ?>

    <div class="filter-bar">
        <form method="GET" class="inline">
            <input type="text" name="cari" value="<?= htmlspecialchars($cari) ?>" placeholder="Cari nama pasien">
            <select name="status">
                <option value="">-- Status --</option>
                <option value="belum dibayar" <?= $status === 'belum dibayar' ? 'selected' : '' ?>>Belum Dibayar</option>
                <option value="menunggu konfirmasi" <?= $status === 'menunggu konfirmasi' ? 'selected' : '' ?>>Menunggu Konfirmasi</option>
                <option value="sudah dibayar" <?= $status === 'sudah dibayar' ? 'selected' : '' ?>>Sudah Dibayar</option>
            </select>
            <select name="metode">
                <option value="">-- Metode --</option>
                <option value="cash" <?= $metode === 'cash' ? 'selected' : '' ?>>Cash</option>
                <option value="transfer" <?= $metode === 'transfer' ? 'selected' : '' ?>>Transfer</option>
            </select>
            <button type="submit">Terapkan</button>
        </form>
    </div>

    <table>
        <tr>
            <th>Pasien</th>
            <th>Dokter</th>
            <th>Tanggal</th>
            <th>Rincian Biaya</th>
            <th>Total</th>
            <th>Status</th>
            <th>Metode</th>
            <th>Bukti Transfer</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = $tagihan_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['nama_pasien']) ?></td>
                <td><?= htmlspecialchars($row['nama_dokter']) ?></td>
                <td><?= $row['tanggal_kontrol'] ?></td>
                <td>
                    Kontrol: Rp <?= number_format($row['biaya_kontrol']) ?><br>
                    Admin: Rp <?= number_format($row['biaya_administrasi']) ?><br>
                    Tambahan: Rp <?= number_format($row['biaya_tambahan']) ?>
                </td>
                <td><strong>Rp <?= number_format($row['total']) ?></strong></td>
                <td class="status-<?= str_replace(' ', '-', strtolower($row['status'])) ?>">
                    <?= ucfirst($row['status']) ?>
                </td>
                <td><?= $row['metode_pembayaran'] ? ucfirst($row['metode_pembayaran']) : '-' ?></td>
                <td>
                    <?php if ($row['bukti_transfer']): ?>
                        <a href="uploads/transfer/<?= $row['bukti_transfer'] ?>" target="_blank">Lihat</a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($row['status'] === 'menunggu konfirmasi'): ?>
                        <a href="?konfirmasi_id=<?= $row['id'] ?>" onclick="return confirm('Konfirmasi pembayaran ini?')">✔ Konfirmasi</a>
                    <?php elseif ($row['status'] === 'sudah dibayar'): ?>
                        ✅ Lunas<br>
                        <a href="cetak_tagihan.php?id=<?= $row['id'] ?>" target="_blank">🧾 Cetak PDF</a>
                    <?php else: ?>
                        <span style="color: gray;">-</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
