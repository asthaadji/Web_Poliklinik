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

// Handle request untuk CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'edit') {
        $id = $_POST['id'];
        $hari = $_POST['hari'];
        $jam_mulai = $_POST['jam_mulai'];
        $jam_selesai = $_POST['jam_selesai'];
        $status = $_POST['status']; // Status diambil dari form

        $stmt = $conn->prepare("UPDATE jadwal_periksa SET hari = ?, jam_mulai = ?, jam_selesai = ?, status = ? WHERE id = ? AND id_dokter = ?");
        $stmt->bind_param("sssisi", $hari, $jam_mulai, $jam_selesai, $status, $id, $id_dokter);
        if ($stmt->execute()) {
            echo "<script>alert('Jadwal berhasil diperbarui.'); location.reload();</script>";
        } else {
            echo "<script>alert('Gagal memperbarui jadwal: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } elseif ($action === 'fetch') {
        $id = $_POST['id'];

        $stmt = $conn->prepare("SELECT * FROM jadwal_periksa WHERE id = ? AND id_dokter = ?");
        $stmt->bind_param("ii", $id, $id_dokter);
        $stmt->execute();
        $result = $stmt->get_result();
        $jadwal = $result->fetch_assoc();
        echo json_encode($jadwal);
        exit;
    }
    exit;
}

// Query untuk mendapatkan data jadwal periksa milik dokter yang login
$sql = "
    SELECT id, hari, jam_mulai, jam_selesai, status
    FROM jadwal_periksa
    WHERE id_dokter = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_dokter);
$stmt->execute();
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Periksa - Dr. <?= htmlspecialchars($nama_session) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h1>Jadwal Periksa - Dr. <?= htmlspecialchars($nama_session) ?></h1>
    <button class="btn btn-primary mb-3" onclick="showTambah()">+ Tambah Jadwal</button>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Hari</th>
                <th>Jam Mulai</th>
                <th>Jam Selesai</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                $no = 1;
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $no++ . "</td>";
                    echo "<td>" . htmlspecialchars($row['hari']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['jam_mulai']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['jam_selesai']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "<td>
                        <button class='btn btn-warning btn-sm' onclick='showEdit(" . $row['id'] . ")'>Edit</button>
                        <button class='btn btn-danger btn-sm' onclick='deleteJadwal(" . $row['id'] . ")'>Hapus</button>
                    </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' class='text-center'>Tidak ada data</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="jadwalModalTambah" tabindex="-1" aria-labelledby="jadwalModalTambahLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="jadwalModalTambahLabel">Tambah Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formTambah">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="hari_tambah" class="form-label">Hari</label>
                        <input type="text" class="form-control" name="hari" id="hari_tambah" required>
                    </div>
                    <div class="mb-3">
                        <label for="jam_mulai_tambah" class="form-label">Jam Mulai</label>
                        <input type="time" class="form-control" name="jam_mulai" id="jam_mulai_tambah" required>
                    </div>
                    <div class="mb-3">
                        <label for="jam_selesai_tambah" class="form-label">Jam Selesai</label>
                        <input type="time" class="form-control" name="jam_selesai" id="jam_selesai_tambah" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="jadwalModalEdit" tabindex="-1" aria-labelledby="jadwalModalEditLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="jadwalModalEditLabel">Edit Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEdit">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="jadwalId_edit">
                    <div class="mb-3">
                        <label for="hari_edit" class="form-label">Hari</label>
                        <input type="text" class="form-control" name="hari" id="hari_edit" required>
                    </div>
                    <div class="mb-3">
                        <label for="jam_mulai_edit" class="form-label">Jam Mulai</label>
                        <input type="time" class="form-control" name="jam_mulai" id="jam_mulai_edit" required>
                    </div>
                    <div class="mb-3">
                        <label for="jam_selesai_edit" class="form-label">Jam Selesai</label>
                        <input type="time" class="form-control" name="jam_selesai" id="jam_selesai_edit" required>
                    </div>
                    <div class="mb-3">
                        <label for="status_edit" class="form-label">Status</label>
                        <select class="form-select" name="status" id="status_edit" required>
                            <option value="Aktif">Aktif</option>
                            <option value="Non Aktif">Non Aktif</option>
                        </select>
                    </div>


                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function showTambah() {
        document.getElementById('formTambah').reset();
        new bootstrap.Modal(document.getElementById('jadwalModalTambah')).show();
    }

    function showEdit(id) {
    fetch('jadwalperiksa.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ action: 'fetch', id: id })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('jadwalId_edit').value = data.id;
        document.getElementById('hari_edit').value = data.hari;
        document.getElementById('jam_mulai_edit').value = data.jam_mulai;
        document.getElementById('jam_selesai_edit').value = data.jam_selesai;

        // Set status sesuai data dari database
        document.getElementById('status_edit').value = data.status;

        new bootstrap.Modal(document.getElementById('jadwalModalEdit')).show();
    })
    .catch(error => alert('Gagal mengambil data. Silakan coba lagi.'));
}



    document.getElementById('formTambah').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('jadwalperiksa.php', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(response => response.text())
        .then(alert)
        .then(() => location.reload());
    });

    document.getElementById('formEdit').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('jadwalperiksa.php', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(response => response.text())
        .then(alert)
        .then(() => location.reload());
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
