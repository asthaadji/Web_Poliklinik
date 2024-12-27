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

// Handle request untuk CRUD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    if ($action == 'add') {
        // Ambil data dari form
        $nama_poli = $_POST['nama_poli'];
        $keterangan = $_POST['keterangan'];

        // Validasi input
        if (!empty($nama_poli) && !empty($keterangan)) {
            // Masukkan data ke database
            $stmt = $conn->prepare("INSERT INTO poli (nama_poli, keterangan) VALUES (?, ?)");
            $stmt->bind_param("ss", $nama_poli, $keterangan);
            if ($stmt->execute()) {
                echo "Data poli berhasil ditambahkan.";
            } else {
                echo "Gagal menambahkan data poli.";
            }
            $stmt->close();
        } else {
            echo "Semua field harus diisi!";
        }
    } elseif ($action == 'edit') {
        // Update data poli
        $id = $_POST['id'];
        $nama_poli = $_POST['nama_poli'];
        $keterangan = $_POST['keterangan'];

        if (!empty($id) && !empty($nama_poli) && !empty($keterangan)) {
            $stmt = $conn->prepare("UPDATE poli SET nama_poli=?, keterangan=? WHERE id=?");
            $stmt->bind_param("ssi", $nama_poli, $keterangan, $id);
            if ($stmt->execute()) {
                echo "Data poli berhasil diupdate.";
            } else {
                echo "Gagal mengupdate data poli.";
            }
            $stmt->close();
        } else {
            echo "Semua field harus diisi!";
        }
    } elseif ($action == 'delete') {
        // Hapus data poli
        $id = $_POST['id'];

        if (!empty($id)) {
            $stmt = $conn->prepare("DELETE FROM poli WHERE id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "Data poli berhasil dihapus.";
            } else {
                echo "Gagal menghapus data poli.";
            }
            $stmt->close();
        } else {
            echo "ID tidak boleh kosong!";
        }
    } elseif ($action == 'fetch') {
        // Ambil data poli berdasarkan ID
        $id = $_POST['id'];

        if (!empty($id)) {
            $stmt = $conn->prepare("SELECT * FROM poli WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            echo json_encode($data);
            $stmt->close();
        }
    }
    exit;
}

// Ambil data poli
$sql = "SELECT * FROM poli";
$result = $conn->query($sql);
$polis = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $polis[] = $row;
    }
}
?>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <style>
    .container {
        margin-left: 10px; /* Menyesuaikan jarak dengan sidebar */
        max-width: 90%; /* Membatasi lebar konten agar lebih rapi */
    }
</style>

</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Manajemen Poli</h1>
        <button class="btn btn-primary" onclick="showForm('add')">Tambah Poli</button>
    </div>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Poli</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($polis as $key => $poli): ?>
            <tr>
                <td><?= $key + 1 ?></td>
                <td><?= $poli['nama_poli'] ?></td>
                <td><?= $poli['keterangan'] ?></td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="showForm('edit', <?= $poli['id'] ?>)">Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="deletePoli(<?= $poli['id'] ?>)">Hapus</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

    <!-- Modal Form -->
    <div class="modal fade" id="poliModal" tabindex="-1" aria-labelledby="poliModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="poliModalLabel">Tambah/Edit Poli</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form">
                        <input type="hidden" name="id" id="poliId">
                        <input type="hidden" name="action" id="action">
                        <div class="mb-3">
                            <label for="nama_poli" class="form-label">Nama Poli:</label>
                            <input type="text" class="form-control" name="nama_poli" id="nama_poli" required>
                        </div>
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan:</label>
                            <textarea class="form-control" name="keterangan" id="keterangan" rows="3" required></textarea>
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
                // Fetch data poli untuk di-edit
                $.post('poli.php', { action: 'fetch', id: id }, function(data) {
                    let poli = JSON.parse(data);
                    $('#poliId').val(poli.id);
                    $('#nama_poli').val(poli.nama_poli);
                    $('#keterangan').val(poli.keterangan);
                    $('#poliModalLabel').text('Edit Poli');
                });
            } else {
                $('#form')[0].reset();
                $('#poliId').val('');
                $('#poliModalLabel').text('Tambah Poli');
            }
            $('#poliModal').modal('show');
        }

        function deletePoli(id) {
            if (confirm('Yakin ingin menghapus data ini?')) {
                $.post('poli.php', { action: 'delete', id: id }, function(response) {
                    alert(response);
                    location.reload();
                });
            }
        }

        $('#form').on('submit', function(e) {
            e.preventDefault();
            $.post('poli.php', $(this).serialize(), function(response) {
                alert(response);
                location.reload();
            });
        });
    </script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
