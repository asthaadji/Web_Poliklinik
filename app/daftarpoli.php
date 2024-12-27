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
if (!isset($_SESSION['nama']) || !isset($_SESSION['level']) || $_SESSION['level'] !== 'pasien') {
    echo "<script>alert('Anda tidak memiliki akses ke halaman ini. Silakan login sebagai pasien.'); window.location.href='login.php';</script>";
    exit;
}
$nama_session = $_SESSION['nama']; // Nama pasien dari sesi

// Ambil data id_pasien berdasarkan nama dari sesi
$stmt = $conn->prepare("SELECT id FROM pasien WHERE nama = ?");
$stmt->bind_param("s", $nama_session);
$stmt->execute();
$result = $stmt->get_result();
$pasien = $result->fetch_assoc();
if (!$pasien) {
    die("Pasien tidak ditemukan.");
}
$id_pasien = $pasien['id'];

// Ambil data jadwal dokter beserta nama poli
$sql = "
    SELECT 
        jp.id AS id_jadwal, 
        d.nama AS nama_dokter, 
        p.nama_poli AS nama_poli, 
        jp.hari, 
        jp.jam_mulai, 
        jp.jam_selesai
    FROM jadwal_periksa jp
    JOIN dokter d ON jp.id_dokter = d.id
    JOIN poli p ON d.id_poli = p.id
    WHERE jp.status = 'Aktif'
";
$result = $conn->query($sql);

// Proses simpan data pendaftaran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_jadwal = $_POST['id_jadwal'];
    $keluhan = $_POST['keluhan'];
    $tanggal_hari_ini = date('Y-m-d');

    // Periksa nomor antrian untuk tanggal hari ini
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM daftar_poli WHERE id_jadwal = ? AND tanggal_antrian = ?");
    $stmt->bind_param("is", $id_jadwal, $tanggal_hari_ini);
    $stmt->execute();
    $result_antrian = $stmt->get_result();
    $row_antrian = $result_antrian->fetch_assoc();
    $no_antrian = $row_antrian['total'] + 1;

    // Simpan data ke tabel daftar_poli
    $stmt = $conn->prepare("INSERT INTO daftar_poli (id_pasien, id_jadwal, keluhan, no_antrian, tanggal_antrian) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisds", $id_pasien, $id_jadwal, $keluhan, $no_antrian, $tanggal_hari_ini);
    if ($stmt->execute()) {
        echo "<script>alert('Pendaftaran berhasil! No. Antrian Anda: $no_antrian');</script>";
    } else {
        echo "<script>alert('Pendaftaran gagal: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Poli</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h1>Form Tambah Data Pemeriksaan - <?= htmlspecialchars($nama_session) ?></h1>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="jadwal" class="form-label">Pilih Jadwal</label>
            <div class="row">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-6">
                        <label class="card p-3" style="border: 1px solid #ddd;">
                            <input type="radio" name="id_jadwal" value="<?= $row['id_jadwal'] ?>" required>
                            <strong><?= htmlspecialchars($row['nama_dokter']) ?></strong><br>
                            Poli: <?= htmlspecialchars($row['nama_poli']) ?><br>
                            Jadwal: <?= htmlspecialchars($row['hari']) ?>, <?= htmlspecialchars($row['jam_mulai']) ?> - <?= htmlspecialchars($row['jam_selesai']) ?>
                        </label>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <div class="mb-3">
            <label for="keluhan" class="form-label">Keluhan</label>
            <textarea class="form-control" id="keluhan" name="keluhan" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
