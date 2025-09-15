<?php
include 'koneksi.php';
include 'utils.php';
cek_login();

if ($_SESSION['role'] !== 'pasien') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$upload_dir = "uploads/transfer/";

// Ambil ID pasien
$q = $conn->prepare("SELECT id FROM pasien WHERE user_id = ?");
$q->bind_param("i", $user_id);
$q->execute();
$pasien_data = $q->get_result()->fetch_assoc();
$pasien_id = $pasien_data['id'] ?? 0;

// Debug POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<pre style='background:#fffae6;padding:10px;border:1px solid orange;'>DEBUG POST:\n";
    print_r($_POST);
    echo "</pre>";
}

// Proses bayar cash
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bayar_cash'])) {
    if (isset($_POST['tagihan_id']) && is_numeric($_POST['tagihan_id'])) {
        $id = (int) $_POST['tagihan_id'];
        $stmt = $conn->prepare("UPDATE tagihan SET metode_pembayaran = 'cash', status = 'menunggu konfirmasi' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $success = "Pembayaran cash berhasil dikirim untuk konfirmasi.";
        } else {
            $error = "Gagal memperbarui tagihan. Mungkin tidak ada perubahan data.";
        }
    } else {
        $error = "ID tagihan tidak valid.";
    }
}

// Proses bayar transfer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bayar_transfer']) && isset($_FILES['bukti'])) {
    $id = $_POST['tagihan_id'];
    $file = $_FILES['bukti'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_name = uniqid("bukti_") . "." . $ext;
    $target = $upload_dir . $new_name;

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (move_uploaded_file($file['tmp_name'], $target)) {
        $stmt = $conn->prepare("UPDATE tagihan SET metode_pembayaran = 'transfer', bukti_transfer = ?, status = 'menunggu konfirmasi' WHERE id = ?");
        $stmt->bind_param("si", $new_name, $id);
        $stmt->execute();
        $success = "Bukti transfer berhasil dikirim.";
    } else {
        $error = "Upload gagal. Coba file lain.";
    }
}

// Ambil semua tagihan pasien
$stmt = $conn->prepare("
    SELECT t.*, jk.tanggal_kontrol, jk.jam_kontrol, d.nama AS nama_dokter
    FROM tagihan t
    JOIN jadwal_kontrol jk ON t.jadwal_id = jk.id
    JOIN dokter d ON jk.dokter_id = d.id
    WHERE jk.pasien_id = ?
    ORDER BY t.created_at DESC
");
$stmt->bind_param("i", $pasien_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tagihan Saya</title>
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
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }

        th, td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #1b3b2f;
            color: white;
        }

        .status-belum-dibayar { color: red; font-weight: bold; }
        .status-menunggu-konfirmasi { color: orange; font-weight: bold; }
        .status-sudah-dibayar { color: green; font-weight: bold; }

        .alert-success, .alert-error {
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            width: fit-content;
        }

        .alert-success {
            background: #d9ffd9;
            border: 1px solid green;
        }

        .alert-error {
            background: #ffd9d9;
            border: 1px solid red;
        }

        .btn-cash {
            background-color: #388e3c;
            color: white;
        }

        .btn-transfer {
            background-color: #1976d2;
            color: white;
        }

        .form-pembayaran {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .form-pembayaran input[type="file"] {
            margin-top: 5px;
        }

        .radio-metode {
            display: flex;
            flex-direction: column;
            gap: 5px;
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
        <a href="tagihan_pasien.php">Tagihan</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>Tagihan Pemeriksaan Anda</h2>

    <?php if (!empty($success)): ?>
        <div class="alert-success">✅ <?= $success ?></div>
    <?php elseif (!empty($error)): ?>
        <div class="alert-error">⚠️ <?= $error ?></div>
    <?php endif; ?>

    <?php if ($result->num_rows === 0): ?>
        <p>Belum ada tagihan yang tersedia.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Dokter</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>Rincian</th>
                <th>Total</th>
                <th>Status</th>
                <th>Pembayaran</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama_dokter']) ?></td>
                    <td><?= $row['tanggal_kontrol'] ?></td>
                    <td><?= $row['jam_kontrol'] ?: '-' ?></td>
                    <td>
                        Kontrol: Rp <?= number_format($row['biaya_kontrol']) ?><br>
                        Admin: Rp <?= number_format($row['biaya_administrasi']) ?><br>
                        Tambahan: Rp <?= number_format($row['biaya_tambahan']) ?>
                    </td>
                    <td><strong>Rp <?= number_format($row['total']) ?></strong></td>
                    <td class="status-<?= str_replace(' ', '-', strtolower($row['status'])) ?>">
                        <?= ucfirst($row['status']) ?>
                    </td>
                    <td>
<?php if ($row['status'] === 'belum dibayar'): ?>

    <!-- FORM BAYAR CASH -->
    <form method="POST" class="form-pembayaran" style="margin-bottom: 20px;">
        <input type="hidden" name="tagihan_id" value="<?= $row['id'] ?>">
        <button type="submit" name="bayar_cash" value="1" class="btn-cash">💵 Konfirmasi Bayar Cash</button>
    </form>

    <hr style="margin: 10px 0; border: 0; border-top: 1px dashed #ccc;">

    <!-- FORM TRANSFER -->
    <form method="POST" enctype="multipart/form-data" class="form-pembayaran">
        <input type="hidden" name="tagihan_id" value="<?= $row['id'] ?>">
        <label for="bukti-<?= $row['id'] ?>">Upload Bukti Transfer:</label>
        <input type="file" name="bukti" id="bukti-<?= $row['id'] ?>" accept=".jpg,.jpeg,.png" required>
        <button type="submit" name="bayar_transfer" class="btn-transfer">🏦 Kirim Bukti Transfer</button>
    </form>

<?php elseif ($row['status'] === 'menunggu konfirmasi'): ?>
    Metode: <?= ucfirst($row['metode_pembayaran']) ?><br>
    <?php if ($row['bukti_transfer']): ?>
        <a href="<?= $upload_dir . $row['bukti_transfer'] ?>" target="_blank">📷 Lihat Bukti</a><br>
    <?php endif; ?>
    Menunggu konfirmasi admin

<?php elseif ($row['status'] === 'sudah dibayar'): ?>
    ✅ Lunas<br>
    Metode: <?= ucfirst($row['metode_pembayaran']) ?>
<?php endif; ?>

                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
