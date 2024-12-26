<?php
// Koneksi ke database
$host = "localhost";
$user = "root";
$password = "";
$dbname = "poliklinik";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Mulai sesi jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validasi sesi login untuk admin
if (!isset($_SESSION['nama']) || !isset($_SESSION['level']) || $_SESSION['level'] !== 'admin') {
    die("<script>alert('Anda tidak memiliki akses ke halaman ini. Silakan login sebagai admin.'); window.location.href = 'login.php';</script>");
}

// Query untuk mendapatkan data antrian saat ini
$sql = "
    SELECT dp.id, p.nama AS nama_pasien, j.hari, j.jam_mulai, j.jam_selesai, dp.keluhan, dp.no_antrian, dp.tanggal_antrian, dp.status
    FROM daftar_poli dp
    JOIN pasien p ON dp.id_pasien = p.id
    JOIN jadwal_periksa j ON dp.id_jadwal = j.id
    WHERE dp.tanggal_antrian = CURDATE()
    ORDER BY dp.no_antrian ASC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Antrian Pasien - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .status-selesai {
            color: white;
            background-color: green;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
        .status-menunggu {
            color: black;
            background-color: yellow;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h1>Antrian Pasien Hari Ini</h1>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>No</th>
            <th>Nama Pasien</th>
            <th>Jadwal Periksa</th>
            <th>Keluhan</th>
            <th>No Antrian</th>
            <th>Tanggal Antrian</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php $no = 1; ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama_pasien']) ?></td>
                    <td><?= htmlspecialchars($row['hari']) ?> (<?= htmlspecialchars($row['jam_mulai']) ?> - <?= htmlspecialchars($row['jam_selesai']) ?>)</td>
                    <td><?= htmlspecialchars($row['keluhan']) ?></td>
                    <td><?= htmlspecialchars($row['no_antrian']) ?></td>
                    <td><?= htmlspecialchars($row['tanggal_antrian']) ?></td>
                    <td>
                        <?php if ($row['status'] === 'Selesai'): ?>
                            <span class="status-selesai">Selesai</span>
                        <?php elseif ($row['status'] === 'Menunggu'): ?>
                            <span class="status-menunggu">Menunggu</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center">Tidak ada antrian untuk hari ini</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

