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
    $action = $_POST['action'];

    if ($action == 'add') {
        // Ambil data dari form
        $nama_obat = $_POST['nama_obat'];
        $kemasan = $_POST['kemasan'];
        $harga = $_POST['harga'];

        // Validasi input
        if (!empty($nama_obat) && !empty($kemasan) && !empty($harga)) {
            // Masukkan data ke database
            $stmt = $conn->prepare("INSERT INTO obat (nama_obat, kemasan, harga) VALUES (?, ?, ?)");
            $stmt->bind_param("ssd", $nama_obat, $kemasan, $harga);
            if ($stmt->execute()) {
                echo "Data obat berhasil ditambahkan.";
            } else {
                echo "Gagal menambahkan data obat.";
            }
            $stmt->close();
        } else {
            echo "Semua field harus diisi!";
        }
    } elseif ($action == 'edit') {
        // Update data obat
        $id = $_POST['id'];
        $nama_obat = $_POST['nama_obat'];
        $kemasan = $_POST['kemasan'];
        $harga = $_POST['harga'];

        if (!empty($id) && !empty($nama_obat) && !empty($kemasan) && !empty($harga)) {
            $stmt = $conn->prepare("UPDATE obat SET nama_obat=?, kemasan=?, harga=? WHERE id=?");
            $stmt->bind_param("ssdi", $nama_obat, $kemasan, $harga, $id);
            if ($stmt->execute()) {
                echo "Data obat berhasil diupdate.";
            } else {
                echo "Gagal mengupdate data obat.";
            }
            $stmt->close();
        } else {
            echo "Semua field harus diisi!";
        }
    } elseif ($action == 'delete') {
        // Hapus data obat
        $id = $_POST['id'];

        if (!empty($id)) {
            $stmt = $conn->prepare("DELETE FROM obat WHERE id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "Data obat berhasil dihapus.";
            } else {
                echo "Gagal menghapus data obat.";
            }
            $stmt->close();
        } else {
            echo "ID tidak boleh kosong!";
        }
    } elseif ($action == 'fetch') {
        // Ambil data obat berdasarkan ID
        $id = $_POST['id'];

        if (!empty($id)) {
            $stmt = $conn->prepare("SELECT * FROM obat WHERE id=?");
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

// Ambil data obat
$sql = "SELECT * FROM obat";
$result = $conn->query($sql);
$obats = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $obats[] = $row;
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
        <h1>Manajemen Obat</h1>
        <button class="btn btn-primary" onclick="showForm('add')">Tambah Obat</button>
    </div>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Obat</th>
                <th>Kemasan</th>
                <th>Harga</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($obats as $key => $obat): ?>
            <tr>
                <td><?= $key + 1 ?></td>
                <td><?= $obat['nama_obat'] ?></td>
                <td><?= $obat['kemasan'] ?></td>
                <td><?= number_format($obat['harga'], 2, ',', '.') ?></td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="showForm('edit', <?= $obat['id'] ?>)">Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteObat(<?= $obat['id'] ?>)">Hapus</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

    <!-- Modal Form -->
    <div class="modal fade" id="obatModal" tabindex="-1" aria-labelledby="obatModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="obatModalLabel">Tambah/Edit Obat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form">
                        <input type="hidden" name="id" id="obatId">
                        <input type="hidden" name="action" id="action">
                        <div class="mb-3">
                            <label for="nama_obat" class="form-label">Nama Obat:</label>
                            <input type="text" class="form-control" name="nama_obat" id="nama_obat" required>
                        </div>
                        <div class="mb-3">
                            <label for="kemasan" class="form-label">Kemasan:</label>
                            <input type="text" class="form-control" name="kemasan" id="kemasan" required>
                        </div>
                        <div class="mb-3">
                            <label for="harga" class="form-label">Harga:</label>
                            <input type="number" class="form-control" name="harga" id="harga" required min="0" step="0.01">
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
                // Fetch data obat untuk di-edit
                $.post('obat.php', { action: 'fetch', id: id }, function(data) {
                    let obat = JSON.parse(data);
                    $('#obatId').val(obat.id);
                    $('#nama_obat').val(obat.nama_obat);
                    $('#kemasan').val(obat.kemasan);
                    $('#harga').val(obat.harga);
                    $('#obatModalLabel').text('Edit Obat');
                });
            } else {
                $('#form')[0].reset();
                $('#obatId').val('');
                $('#obatModalLabel').text('Tambah Obat');
            }
            $('#obatModal').modal('show');
        }

        function deleteObat(id) {
            if (confirm('Yakin ingin menghapus data ini?')) {
                $.post('obat.php', { action: 'delete', id: id }, function(response) {
                    alert(response);
                    location.reload();
                });
            }
        }

        $('#form').on('submit', function(e) {
            e.preventDefault();
            $.post('obat.php', $(this).serialize(), function(response) {
                alert(response);
                location.reload();
            });
        });
    </script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
