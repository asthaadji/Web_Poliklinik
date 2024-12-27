<?php

// Koneksi ke database
$host = "localhost";
$user = "root";
$password = "";
$dbname = "poli";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Validasi sesi login
if (!isset($_SESSION['nama']) || !isset($_SESSION['level']) || $_SESSION['level'] !== 'dokter') {
    die("Anda tidak memiliki akses ke halaman ini. Silakan login sebagai dokter.");
}
$nama_session = $_SESSION['nama']; // Nama dokter dari sesi
$level = $_SESSION['level'];

// Ambil data dokter untuk ditampilkan pada form edit
$query = "SELECT * FROM dokter WHERE nama = ? AND level = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $nama_session, $level);
$stmt->execute();
$result = $stmt->get_result();
$dokter = $result->fetch_assoc();
$stmt->close();

if (!$dokter) {
    die("Data dokter tidak ditemukan. Silakan login ulang.");
}

// Ambil daftar poli untuk dropdown
$query_poli = "SELECT * FROM poli";
$result_poli = $conn->query($query_poli);
$polis = [];
while ($row = $result_poli->fetch_assoc()) {
    $polis[] = $row;
}

// Update data dokter jika form disubmit
$success_message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_baru = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $no_hp = $_POST['no_hp'];
    $id_poli = $_POST['id_poli'];

    $update_query = "UPDATE dokter SET nama = ?, alamat = ?, no_hp = ?, id_poli = ? WHERE nama = ? AND level = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssiss", $nama_baru, $alamat, $no_hp, $id_poli, $nama_session, $level);
    if ($stmt->execute()) {
        $_SESSION['nama'] = $nama_baru; // Perbarui nama di sesi
        $success_message = "Data berhasil diperbarui.";
    } else {
        $success_message = "Gagal mengupdate data.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>Edit Profil Dokter</h2>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success" role="alert">
            <?= htmlspecialchars($success_message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label for="nama" class="form-label">Nama</label>
            <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($dokter['nama']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="alamat" class="form-label">Alamat</label>
            <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?= htmlspecialchars($dokter['alamat']) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="no_hp" class="form-label">No HP</label>
            <input type="text" class="form-control" id="no_hp" name="no_hp" value="<?= htmlspecialchars($dokter['no_hp']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="id_poli" class="form-label">Poliklinik</label>
            <select class="form-select" id="id_poli" name="id_poli" required>
                <option value="" disabled>Pilih Poliklinik</option>
                <?php foreach ($polis as $poli): ?>
                    <option value="<?= $poli['id'] ?>" <?= $dokter['id_poli'] == $poli['id'] ? 'selected' : '' ?>><?= htmlspecialchars($poli['nama_poli']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
