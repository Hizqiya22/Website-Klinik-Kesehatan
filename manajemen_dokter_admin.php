<?php 
include 'koneksi.php';
include 'utils.php';
cek_login();

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Filter pencarian dan spesialis
$keyword = $_GET['keyword'] ?? '';
$filter_spesialis = $_GET['spesialis'] ?? '';

// Proses hapus dokter
if (isset($_GET['hapus'])) {
    $dokter_id = $_GET['hapus'];
    $q = $conn->prepare("SELECT user_id FROM dokter WHERE id = ?");
    $q->bind_param("i", $dokter_id);
    $q->execute();
    $result = $q->get_result()->fetch_assoc();
    $user_id = $result['user_id'] ?? 0;
    if ($user_id) {
        $conn->query("DELETE FROM dokter WHERE id = $dokter_id");
        $conn->query("DELETE FROM users WHERE id = $user_id");
        $success = "Dokter berhasil dihapus.";
    }
}

// Proses edit dokter
if (isset($_POST['edit_id'])) {
    $edit_id = $_POST['edit_id'];
    $nama = $_POST['nama'];
    $spesialis = $_POST['spesialis'];
    $no_hp = $_POST['no_hp'];
    $stmt = $conn->prepare("UPDATE dokter SET nama = ?, spesialis = ?, no_hp = ? WHERE id = ?");
    $stmt->bind_param("sssi", $nama, $spesialis, $no_hp, $edit_id);
    $stmt->execute();
    $success = "Data dokter berhasil diperbarui.";
}

// Proses tambah dokter baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['edit_id'])) {
    $nama = $_POST['nama'];
    $spesialis = $_POST['spesialis'];
    $no_hp = $_POST['no_hp'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $cek = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $cek->bind_param("s", $username);
    $cek->execute();
    $cek_result = $cek->get_result();
    if ($cek_result->num_rows > 0) {
        $error = "Username sudah digunakan.";
    } else {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt_user = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'dokter')");
        $stmt_user->bind_param("ss", $username, $password_hashed);
        $stmt_user->execute();
        $user_id = $conn->insert_id;
        $stmt_dokter = $conn->prepare("INSERT INTO dokter (user_id, nama, spesialis, no_hp) VALUES (?, ?, ?, ?)");
        $stmt_dokter->bind_param("isss", $user_id, $nama, $spesialis, $no_hp);
        $stmt_dokter->execute();
        $success = "Dokter berhasil ditambahkan.";
    }
}

// Ambil data dokter
$where = [];
if (!empty($keyword)) {
    $keyword_escaped = $conn->real_escape_string($keyword);
    $where[] = "d.nama LIKE '%$keyword_escaped%'";
}
if (!empty($filter_spesialis)) {
    $filter_spesialis_escaped = $conn->real_escape_string($filter_spesialis);
    $where[] = "d.spesialis = '$filter_spesialis_escaped'";
}
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";
$dokter_result = $conn->query("SELECT d.*, u.username FROM dokter d JOIN users u ON d.user_id = u.id $where_sql ORDER BY d.nama ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manajemen Dokter</title>
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
        }

        th {
            background: #1b3b2f;
            color: white;
        }

        form input, select {
            padding: 8px;
            margin-top: 5px;
            width: 300px;
            display: block;
        }

        form button {
            margin-top: 15px;
            padding: 10px 20px;
            background: #1b3b2f;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        .alert-success {
            background: #d9ffd9;
            border: 1px solid green;
            padding: 10px;
            margin-bottom: 15px;
        }

        .alert-error {
            background: #ffd9d9;
            border: 1px solid red;
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
    <?php elseif (!empty($error)): ?>
        <div class="alert-error">⚠️ <?= $error ?></div>
    <?php endif; ?>

    <h3>Tambah Dokter Baru</h3>
    <form method="POST">
        <label>Nama Dokter</label>
        <input type="text" name="nama" required>

        <label>Spesialisasi</label>
        <select name="spesialis" required>
            <option value="">-- Pilih --</option>
            <option value="Umum">Umum</option>
            <option value="Spesialis">Spesialis</option>
        </select>

        <label>No HP</label>
        <input type="text" name="no_hp">

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="text" name="password" required>

        <button type="submit">Simpan</button>
    </form>

    <h3>Filter Dokter</h3>
    <form method="GET" class="filter-form">
        <input type="text" name="keyword" placeholder="Cari nama dokter" value="<?= htmlspecialchars($keyword) ?>">
        <select name="spesialis">
            <option value="">-- Semua Spesialis --</option>
            <option value="Umum" <?= $filter_spesialis === 'Umum' ? 'selected' : '' ?>>Umum</option>
            <option value="Spesialis" <?= $filter_spesialis === 'Spesialis' ? 'selected' : '' ?>>Spesialis</option>
        </select>
        <button type="submit">🔍 Cari</button>
    </form>

    <h3>Daftar Dokter</h3>
    <table>
        <tr>
            <th>Nama</th>
            <th>Spesialisasi</th>
            <th>No HP</th>
            <th>Username</th>
            <th>Aksi</th>
        </tr>
        <?php while ($dokter = $dokter_result->fetch_assoc()): ?>
            <tr>
                <form method="POST">
                    <td>
                        <input type="hidden" name="edit_id" value="<?= $dokter['id'] ?>">
                        <input type="text" name="nama" value="<?= htmlspecialchars($dokter['nama']) ?>" required>
                    </td>
                    <td>
                        <select name="spesialis" required>
                            <option value="Umum" <?= $dokter['spesialis'] === 'Umum' ? 'selected' : '' ?>>Umum</option>
                            <option value="Spesialis" <?= $dokter['spesialis'] === 'Spesialis' ? 'selected' : '' ?>>Spesialis</option>
                        </select>
                    </td>
                    <td><input type="text" name="no_hp" value="<?= htmlspecialchars($dokter['no_hp']) ?>"></td>
                    <td><?= htmlspecialchars($dokter['username']) ?></td>
                    <td>
                        <button type="submit">💾 Simpan</button>
                        <a href="?hapus=<?= $dokter['id'] ?>" onclick="return confirm('Hapus dokter ini?')" style="color: red; text-decoration: none; font-weight: bold;">🗑 Hapus</a>
                    </td>
                </form>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
