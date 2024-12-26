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

// Validasi sesi login (khusus untuk admin)
if (!isset($_SESSION['nama']) || !isset($_SESSION['level']) || $_SESSION['level'] !== 'admin') {
    die("<script>alert('Anda tidak memiliki akses ke halaman ini. Silakan login sebagai admin.'); window.location.href = 'login.php';</script>");
}

// Handle request untuk CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'add') {
        $id_dokter = $_POST['id_dokter'];
        $hari = $_POST['hari'];
        $jam_mulai = $_POST['jam_mulai'];
        $jam_selesai = $_POST['jam_selesai'];
        $status = "Aktif"; // Status default

        $stmt = $conn->prepare("INSERT INTO jadwal_periksa (id_dokter, hari, jam_mulai, jam_selesai, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $id_dokter, $hari, $jam_mulai, $jam_selesai, $status);
        if ($stmt->execute()) {
            echo "<script>alert('Jadwal berhasil ditambahkan.'); location.reload();</script>";
        } else {
            echo "<script>alert('Gagal menambahkan jadwal: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } elseif ($action === 'edit') {
        $id = $_POST['id'];
        $id_dokter = $_POST['id_dokter'];
        $hari = $_POST['hari'];
        $jam_mulai = $_POST['jam_mulai'];
        $jam_selesai = $_POST['jam_selesai'];
        $status = $_POST['status'];

        $stmt = $conn->prepare("UPDATE jadwal_periksa SET id_dokter = ?, hari = ?, jam_mulai = ?, jam_selesai = ?, status = ? WHERE id = ?");
        $stmt->bind_param("issssi", $id_dokter, $hari, $jam_mulai, $jam_selesai, $status, $id);
        if ($stmt->execute()) {
            echo "<script>alert('Jadwal berhasil diperbarui.'); location.reload();</script>";
        } else {
            echo "<script>alert('Gagal memperbarui jadwal: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } elseif ($action === 'delete') {
        $id = $_POST['id'];

        $stmt = $conn->prepare("DELETE FROM jadwal_periksa WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "<script>alert('Jadwal berhasil dihapus.'); location.reload();</script>";
        } else {
            echo "<script>alert('Gagal menghapus jadwal: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } elseif ($action === 'fetch') {
        $id = $_POST['id'];

        $stmt = $conn->prepare("SELECT * FROM jadwal_periksa WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $jadwal = $result->fetch_assoc();
        echo json_encode($jadwal);
        exit;
    }
    exit;
}

// Query untuk mendapatkan semua data jadwal periksa
$sql = "
    SELECT jp.id, jp.hari, jp.jam_mulai, jp.jam_selesai, jp.status, d.nama AS nama_dokter
    FROM jadwal_periksa jp
    JOIN dokter d ON jp.id_dokter = d.id
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Periksa - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h1>Jadwal Periksa - Admin</h1>
    <button class="btn btn-primary mb-3" onclick="showTambah()">+ Tambah Jadwal</button>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>No</th>
            <th>Dokter</th>
            <th>Hari</th>
            <th>Jam Mulai</th>
            <th>Jam Selesai</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php $no = 1; ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama_dokter']) ?></td>
                    <td><?= htmlspecialchars($row['hari']) ?></td>
                    <td><?= htmlspecialchars($row['jam_mulai']) ?></td>
                    <td><?= htmlspecialchars($row['jam_selesai']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="showEdit(<?= $row['id'] ?>)">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteJadwal(<?= $row['id'] ?>)">Hapus</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center">Tidak ada data jadwal</td>
            </tr>
        <?php endif; ?>
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
                        <label for="dokter_tambah" class="form-label">Dokter</label>
                        <input type="text" class="form-control" name="id_dokter" id="dokter_tambah" required>
                    </div>
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
                        <label for="dokter_edit" class="form-label">Dokter</label>
                        <input type="text" class="form-control" name="id_dokter" id="dokter_edit" required>
                    </div>
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
            document.getElementById('dokter_edit').value = data.id_dokter;
            document.getElementById('hari_edit').value = data.hari;
            document.getElementById('jam_mulai_edit').value = data.jam_mulai;
            document.getElementById('jam_selesai_edit').value = data.jam_selesai;
            document.getElementById('status_edit').value = data.status;
            new bootstrap.Modal(document.getElementById('jadwalModalEdit')).show();
        });
    }

    function deleteJadwal(id) {
        if (confirm('Yakin ingin menghapus jadwal ini?')) {
            fetch('jadwalperiksa.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'delete', id: id })
            })
            .then(response => response.text())
            .then(alert)
            .then(() => location.reload());
        }
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
