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

// Query daftar pasien
$sql_pasien = "
    SELECT dp.id AS id_daftar, p.nama AS nama_pasien, dp.keluhan, dp.no_antrian, jp.hari, jp.jam_mulai, jp.jam_selesai 
    FROM daftar_poli dp
    JOIN pasien p ON dp.id_pasien = p.id
    JOIN jadwal_periksa jp ON dp.id_jadwal = jp.id
    WHERE jp.id_dokter = ? AND dp.status = 'Menunggu'
";
$stmt_pasien = $conn->prepare($sql_pasien);
$stmt_pasien->bind_param("i", $id_dokter);
$stmt_pasien->execute();
$result_pasien = $stmt_pasien->get_result();

// Query untuk mendapatkan daftar obat
$sql_obat = "SELECT id, nama_obat, kemasan, harga FROM obat";
$result_obat = $conn->query($sql_obat);

// Proses simpan data pemeriksaan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_periksa'])) {
    $id_daftar_poli = $_POST['id_daftar_poli'];
    $catatan = $_POST['catatan'];
    $biaya_periksa = 150000; // Biaya jasa dokter default
    $id_obat = $_POST['id_obat'];

    // Ambil harga obat
    $stmt_obat = $conn->prepare("SELECT harga FROM obat WHERE id = ?");
    $stmt_obat->bind_param("i", $id_obat);
    $stmt_obat->execute();
    $result_obat = $stmt_obat->get_result();
    $obat = $result_obat->fetch_assoc();
    $harga_obat = $obat['harga'];

    // Total biaya
    $total_biaya = $biaya_periksa + $harga_obat;

    // Simpan data ke tabel periksa
    $stmt_periksa = $conn->prepare("INSERT INTO periksa (id_daftar_poli, tgl_periksa, catatan, biaya_periksa) VALUES (?, NOW(), ?, ?)");
    $stmt_periksa->bind_param("isi", $id_daftar_poli, $catatan, $total_biaya);

    if ($stmt_periksa->execute()) {
        // Simpan data ke tabel detail_periksa
        $id_periksa = $stmt_periksa->insert_id;
        $stmt_detail = $conn->prepare("INSERT INTO detail_periksa (id_periksa, id_obat) VALUES (?, ?)");
        $stmt_detail->bind_param("ii", $id_periksa, $id_obat);
        $stmt_detail->execute();

        // Update status daftar_poli
        $stmt_update = $conn->prepare("UPDATE daftar_poli SET status = 'Selesai' WHERE id = ?");
        $stmt_update->bind_param("i", $id_daftar_poli);
        $stmt_update->execute();

        echo "<script>alert('Pemeriksaan berhasil disimpan. Total biaya: Rp$total_biaya'); window.location.href = 'periksadokter.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan pemeriksaan.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Periksa Dokter</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h1>Periksa Dokter - Dr. <?= htmlspecialchars($nama_session) ?></h1>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>No Antrian</th>
            <th>Nama Pasien</th>
            <th>Keluhan</th>
            <th>Jadwal</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result_pasien->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['no_antrian']) ?></td>
                <td><?= htmlspecialchars($row['nama_pasien']) ?></td>
                <td><?= htmlspecialchars($row['keluhan']) ?></td>
                <td><?= htmlspecialchars($row['hari']) ?>, <?= htmlspecialchars($row['jam_mulai']) ?> - <?= htmlspecialchars($row['jam_selesai']) ?></td>
                <td>
                    <button class="btn btn-primary btn-sm" onclick="showPeriksaModal(<?= htmlspecialchars($row['id_daftar']) ?>, '<?= htmlspecialchars($row['nama_pasien']) ?>', '<?= htmlspecialchars($row['keluhan']) ?>')">Periksa</button>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal Periksa -->
<div class="modal fade" id="periksaModal" tabindex="-1" aria-labelledby="periksaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="periksaModalLabel">Form Pemeriksaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id_daftar_poli" id="id_daftar_poli">
                    <div class="mb-3">
                        <label for="nama_pasien" class="form-label">Nama Pasien</label>
                        <input type="text" class="form-control" id="nama_pasien" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="keluhan" class="form-label">Keluhan</label>
                        <textarea class="form-control" id="keluhan" readonly></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="catatan" class="form-label">Catatan Kesehatan</label>
                        <textarea class="form-control" name="catatan" id="catatan" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="id_obat" class="form-label">Obat</label>
                        <select name="id_obat" id="id_obat" class="form-select" required>
                            <?php while ($obat = $result_obat->fetch_assoc()): ?>
                                <option value="<?= $obat['id'] ?>"><?= $obat['nama_obat'] ?> - Rp<?= $obat['harga'] ?> (<?= $obat['kemasan'] ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="submit_periksa" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function showPeriksaModal(id, nama, keluhan) {
        document.getElementById('id_daftar_poli').value = id;
        document.getElementById('nama_pasien').value = nama;
        document.getElementById('keluhan').value = keluhan;
        new bootstrap.Modal(document.getElementById('periksaModal')).show();
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
