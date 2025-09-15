<?php
include 'koneksi.php';
include 'utils.php';
cek_login();

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Filter
$keyword = $_GET['keyword'] ?? '';
$jenis_kelamin = $_GET['jk'] ?? '';

// Hapus pasien
if (isset($_GET['hapus'])) {
    $pasien_id = $_GET['hapus'];
    $q = $conn->prepare("SELECT user_id FROM pasien WHERE id = ?");
    $q->bind_param("i", $pasien_id);
    $q->execute();
    $res = $q->get_result()->fetch_assoc();
    $user_id = $res['user_id'] ?? 0;
    if ($user_id) {
        $conn->query("DELETE FROM pasien WHERE id = $pasien_id");
        $conn->query("DELETE FROM users WHERE id = $user_id");
        $success = "Pasien berhasil dihapus.";
    }
}

// Edit pasien
if (isset($_POST['edit_id'])) {
    $id = $_POST['edit_id'];
    $nama = $_POST['nama'];
    $tgl = $_POST['tanggal_lahir'];
    $jk = $_POST['jenis_kelamin'];
    $no_hp = $_POST['no_hp'];
    $alamat = $_POST['alamat'];

    $stmt = $conn->prepare("UPDATE pasien SET nama=?, tanggal_lahir=?, jenis_kelamin=?, no_hp=?, alamat=? WHERE id=?");
    $stmt->bind_param("sssssi", $nama, $tgl, $jk, $no_hp, $alamat, $id);
    $stmt->execute();
    $success = "Data pasien berhasil diperbarui.";
}

$where = [];
if (!empty($keyword)) {
    $keyword_escaped = $conn->real_escape_string($keyword);
    $where[] = "p.nama LIKE '%$keyword_escaped%'";
}
if (!empty($jenis_kelamin)) {
    $jk_escaped = $conn->real_escape_string($jenis_kelamin);
    $where[] = "p.jenis_kelamin = '$jk_escaped'";
}
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

$pasien_result = $conn->query("SELECT p.*, u.username FROM pasien p JOIN users u ON p.user_id = u.id $where_sql ORDER BY p.nama ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manajemen Pasien</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #1b3b2f;
            color: white;
        }

        input, select {
            width: 100%;
            padding: 5px;
        }

        .alert-success {
            background: #d9ffd9;
            border: 1px solid green;
            padding: 10px;
            margin-bottom: 15px;
        }

        .filter-form {
            margin-top: 20px;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }

        .filter-form input, .filter-form select {
            padding: 8px;
            width: 200px;
        }

        .filter-form button {
            background: #1b3b2f;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
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
    <?php if (!empty($success)): ?>
        <div class="alert-success">✅ <?= $success ?></div>
    <?php endif; ?>

    <h3>Filter Pasien</h3>
    <form method="GET" class="filter-form">
        <input type="text" name="keyword" placeholder="Cari nama pasien" value="<?= htmlspecialchars($keyword) ?>">
        <select name="jk">
            <option value="">-- Semua Jenis Kelamin --</option>
            <option value="L" <?= $jenis_kelamin === 'L' ? 'selected' : '' ?>>Laki-laki</option>
            <option value="P" <?= $jenis_kelamin === 'P' ? 'selected' : '' ?>>Perempuan</option>
        </select>
        <button type="submit">🔍 Cari</button>
    </form>

    <table>
        <tr>
            <th>Nama</th>
            <th>Tanggal Lahir</th>
            <th>Jenis Kelamin</th>
            <th>No HP</th>
            <th>Alamat</th>
            <th>Username</th>
            <th>Aksi</th>
        </tr>
        <?php while ($p = $pasien_result->fetch_assoc()): ?>
        <tr>
            <form method="POST">
                <input type="hidden" name="edit_id" value="<?= $p['id'] ?>">
                <td><input type="text" name="nama" value="<?= htmlspecialchars($p['nama']) ?>" required></td>
                <td><input type="date" name="tanggal_lahir" value="<?= $p['tanggal_lahir'] ?>"></td>
                <td>
                    <select name="jenis_kelamin">
                        <option value="L" <?= $p['jenis_kelamin'] == 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= $p['jenis_kelamin'] == 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </td>
                <td><input type="text" name="no_hp" value="<?= htmlspecialchars($p['no_hp']) ?>"></td>
                <td><input type="text" name="alamat" value="<?= htmlspecialchars($p['alamat']) ?>"></td>
                <td><?= htmlspecialchars($p['username']) ?></td>
                <td>
                    <button type="submit">💾 Simpan</button>
                    <a href="?hapus=<?= $p['id'] ?>" onclick="return confirm('Hapus pasien ini?')" style="color:red; font-weight:bold; text-decoration:none;">🗑 Hapus</a>
                </td>
            </form>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
