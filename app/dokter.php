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

// Handle request untuk CRUD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action == 'add') {
        // Ambil data dari form
        $nama = trim($_POST['nama']);
        $alamat = trim($_POST['alamat']);
        $no_hp = trim($_POST['no_hp']);
        $id_poli = intval(trim($_POST['id_poli']));
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // Validasi input
        if ($nama && $alamat && $no_hp && $id_poli && $username && $password) {
            $stmt = $conn->prepare("INSERT INTO dokter (nama, alamat, no_hp, id_poli, username, password, level) VALUES (?, ?, ?, ?, ?, ?, 'dokter')");
            $stmt->bind_param("sssiss", $nama, $alamat, $no_hp, $id_poli, $username, $password);
            if ($stmt->execute()) {
                echo "Data berhasil ditambahkan.";
            } else {
                echo "Gagal menambahkan data.";
            }
            $stmt->close();
        } else {
            echo "Semua field harus diisi!";
        }
    } elseif ($action == 'edit') {
        // Update data
        $id = intval(trim($_POST['id']));
        $nama = trim($_POST['nama']);
        $alamat = trim($_POST['alamat']);
        $no_hp = trim($_POST['no_hp']);
        $id_poli = intval(trim($_POST['id_poli']));
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        if ($id && $nama && $alamat && $no_hp && $id_poli && $username && $password) {
            $stmt = $conn->prepare("UPDATE dokter SET nama=?, alamat=?, no_hp=?, id_poli=?, username=?, password=? WHERE id=?");
            $stmt->bind_param("sssissi", $nama, $alamat, $no_hp, $id_poli, $username, $password, $id);
            if ($stmt->execute()) {
                echo "Data berhasil diupdate.";
            } else {
                echo "Gagal mengupdate data.";
            }
            $stmt->close();
        } else {
            echo "Semua field harus diisi!";
        }
    } elseif ($action == 'delete') {
        // Hapus data
        $id = intval(trim($_POST['id']));

        if ($id) {
            $stmt = $conn->prepare("DELETE FROM dokter WHERE id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "Data berhasil dihapus.";
            } else {
                echo "Gagal menghapus data.";
            }
            $stmt->close();
        } else {
            echo "ID tidak boleh kosong!";
        }
    }
    exit;
}

// Ambil data dokter
$sql = "SELECT dokter.id, dokter.nama, dokter.alamat, dokter.no_hp, dokter.username, dokter.password, poli.nama_poli, poli.id AS poli_id 
        FROM dokter 
        INNER JOIN poli ON dokter.id_poli = poli.id";
$result = $conn->query($sql);
$doctors = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
}

// Ambil data poli untuk dropdown
$sql_poli = "SELECT id, nama_poli FROM poli";
$result_poli = $conn->query($sql_poli);
$polis = [];
if ($result_poli->num_rows > 0) {
    while ($row = $result_poli->fetch_assoc()) {
        $polis[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Dokter</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Manajemen Dokter</h1>
        <button class="btn btn-primary" onclick="showForm('add')">Tambah Dokter</button>
    </div>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Alamat</th>
                <th>No HP</th>
                <th>Poli</th>
                <th>Username</th>
                <th>Password</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($doctors as $key => $doctor): ?>
            <tr>
                <td><?= $key + 1 ?></td>
                <td><?= $doctor['nama'] ?></td>
                <td><?= $doctor['alamat'] ?></td>
                <td><?= $doctor['no_hp'] ?></td>
                <td><?= $doctor['nama_poli'] ?></td>
                <td><?= $doctor['username'] ?></td>
                <td><?= $doctor['password'] ?></td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="showForm('edit', <?= $doctor['id'] ?>)">Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteDoctor(<?= $doctor['id'] ?>)">Hapus</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Form -->
<div class="modal fade" id="doctorModal" tabindex="-1" aria-labelledby="doctorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="doctorModalLabel">Tambah/Edit Dokter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form">
                    <input type="hidden" name="id" id="doctorId">
                    <input type="hidden" name="action" id="action">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama:</label>
                        <input type="text" class="form-control" name="nama" id="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat:</label>
                        <input type="text" class="form-control" name="alamat" id="alamat" required>
                    </div>
                    <div class="mb-3">
                        <label for="no_hp" class="form-label">No HP:</label>
                        <input type="text" class="form-control" name="no_hp" id="no_hp" required>
                    </div>
                    <div class="mb-3">
                        <label for="id_poli" class="form-label">Poli:</label>
                        <select class="form-select" name="id_poli" id="id_poli" required>
                            <option value="" disabled selected>Pilih Poli</option>
                            <?php foreach ($polis as $poli): ?>
                                <option value="<?= $poli['id'] ?>"><?= $poli['nama_poli'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username:</label>
                        <input type="text" class="form-control" name="username" id="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password:</label>
                        <input type="password" class="form-control" name="password" id="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function showForm(action, id = null) {
        $('#action').val(action);
        if (action === 'edit' && id !== null) {
            $.post('dokter.php', { action: 'fetch', id: id }, function(data) {
                let doctor = JSON.parse(data);
                $('#doctorId').val(doctor.id);
                $('#nama').val(doctor.nama);
                $('#alamat').val(doctor.alamat);
                $('#no_hp').val(doctor.no_hp);
                $('#id_poli').val(doctor.poli_id);
                $('#username').val(doctor.username);
                $('#password').val(doctor.password);
            });
        } else {
            $('#form')[0].reset(); // Reset form untuk Add
        }
        $('#doctorModal').modal('show');
    }

    function deleteDoctor(id) {
        if (confirm('Yakin ingin menghapus data ini?')) {
            $.post('dokter.php', { action: 'delete', id: id }, function(response) {
                alert(response);
                location.reload();
            });
        }
    }

    $('#form').on('submit', function(e) {
        e.preventDefault();
        $.post('dokter.php', $(this).serialize(), function(response) {
            alert(response);
            location.reload();
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
