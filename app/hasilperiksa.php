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

// Mulai sesi jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validasi sesi login
if (!isset($_SESSION['nama']) || !isset($_SESSION['level']) || $_SESSION['level'] !== 'pasien') {
    die("<script>alert('Anda tidak memiliki akses ke halaman ini. Silakan login sebagai pasien.'); window.location.href = 'login.php';</script>");
}

$nama_session = $_SESSION['nama'];

// Ambil ID pasien berdasarkan nama dari sesi
$stmt = $conn->prepare("SELECT id FROM pasien WHERE nama = ?");
$stmt->bind_param("s", $nama_session);
$stmt->execute();
$result = $stmt->get_result();
$pasien = $result->fetch_assoc();
if (!$pasien) {
    die("<script>alert('Data pasien tidak ditemukan.'); window.location.href = 'login.php';</script>");
}
$id_pasien = $pasien['id'];

// Ambil data riwayat periksa berdasarkan ID pasien
$sql = "
    SELECT 
        p.tgl_periksa, 
        p.catatan, 
        p.biaya_periksa, 
        o.nama_obat, 
        o.kemasan, 
        o.harga
    FROM detail_periksa dp
    JOIN periksa p ON dp.id_periksa = p.id
    JOIN obat o ON dp.id_obat = o.id
    JOIN daftar_poli dpoli ON p.id_daftar_poli = dpoli.id
    WHERE dpoli.id_pasien = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_pasien);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Periksa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h1>Riwayat Periksa - <?= htmlspecialchars($nama_session) ?></h1>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>No</th>
            <th>Tanggal Periksa</th>
            <th>Catatan Dokter</th>
            <th>Obat</th>
            <th>Kemasan</th>
            <th>Harga Obat</th>
            <th>Biaya Periksa</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php $no = 1; ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['tgl_periksa']) ?></td>
                    <td><?= htmlspecialchars($row['catatan']) ?></td>
                    <td><?= htmlspecialchars($row['nama_obat']) ?></td>
                    <td><?= htmlspecialchars($row['kemasan']) ?></td>
                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                    <td>Rp <?= number_format($row['biaya_periksa'], 0, ',', '.') ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center">Belum ada riwayat periksa yang tersedia.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

