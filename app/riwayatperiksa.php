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
if (!isset($_SESSION['nama']) || !isset($_SESSION['level']) || $_SESSION['level'] !== 'dokter') {
    die("<script>alert('Anda tidak memiliki akses ke halaman ini. Silakan login sebagai dokter.'); window.location.href = 'login.php';</script>");
}

$nama_session = $_SESSION['nama'];

// Ambil ID dokter berdasarkan nama dari tabel dokter
$stmt = $conn->prepare("SELECT id FROM dokter WHERE nama = ?");
$stmt->bind_param("s", $nama_session);
$stmt->execute();
$result = $stmt->get_result();
$dokter = $result->fetch_assoc();

if (!$dokter) {
    die("<script>alert('Data dokter tidak ditemukan di database.'); window.location.href = 'login.php';</script>");
}
$id_dokter = $dokter['id'];

// Query untuk mendapatkan riwayat periksa pasien
$sql = "
    SELECT 
        dp.id AS id_detail,
        p.nama AS nama_pasien,
        pr.tgl_periksa,
        pr.catatan,
        o.nama_obat,
        o.harga AS harga_obat,
        pr.biaya_periksa
    FROM detail_periksa dp
    JOIN periksa pr ON dp.id_periksa = pr.id
    JOIN daftar_poli dpoli ON pr.id_daftar_poli = dpoli.id
    JOIN pasien p ON dpoli.id_pasien = p.id
    JOIN obat o ON dp.id_obat = o.id
    JOIN jadwal_periksa jp ON dpoli.id_jadwal = jp.id
    WHERE jp.id_dokter = ?
    ORDER BY pr.tgl_periksa DESC
";
$stmt_riwayat = $conn->prepare($sql);
$stmt_riwayat->bind_param("i", $id_dokter);
$stmt_riwayat->execute();
$result_riwayat = $stmt_riwayat->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Periksa Pasien</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h1>Riwayat Periksa Pasien - Dr. <?= htmlspecialchars($nama_session) ?></h1>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>No</th>
            <th>Nama Pasien</th>
            <th>Tanggal Periksa</th>
            <th>Catatan</th>
            <th>Obat Diberikan</th>
            <th>Harga Obat</th>
            <th>Total Biaya</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($result_riwayat->num_rows > 0): ?>
            <?php $no = 1; ?>
            <?php while ($row = $result_riwayat->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama_pasien']) ?></td>
                    <td><?= htmlspecialchars($row['tgl_periksa']) ?></td>
                    <td><?= htmlspecialchars($row['catatan']) ?></td>
                    <td><?= htmlspecialchars($row['nama_obat']) ?></td>
                    <td>Rp<?= number_format($row['harga_obat'], 0, ',', '.') ?></td>
                    <td>Rp<?= number_format($row['biaya_periksa'], 0, ',', '.') ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center">Tidak ada data riwayat periksa</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
