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

// Fungsi untuk menghasilkan nomor rekam medis (RM)
function generateNoRM($no_ktp)
{
    $awal = substr($no_ktp, 0, 4); // 4 angka awal no_ktp
    $akhir = substr($no_ktp, -2);  // 2 angka akhir no_ktp
    return "RM-$awal-$akhir";
}

// Handle request untuk CRUD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    if ($action == 'add') {
        // Ambil data dari form
        $nama = $_POST['nama'];
        $alamat = $_POST['alamat'];
        $no_ktp = $_POST['no_ktp'];
        $no_hp = $_POST['no_hp'];
        $no_rm = generateNoRM($no_ktp); // Menghasilkan nomor rekam medis dari no_ktp

        // Validasi input
        if (!empty($nama) && !empty($alamat) && !empty($no_ktp) && !empty($no_hp)) {
            // Masukkan data ke database
            $stmt = $conn->prepare("INSERT INTO pasien (nama, alamat, no_ktp, no_hp, no_rm) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nama, $alamat, $no_ktp, $no_hp, $no_rm);
            if ($stmt->execute()) {
                echo "Data pasien berhasil ditambahkan.";
            } else {
                echo "Gagal menambahkan data pasien.";
            }
            $stmt->close();
        } else {
            echo "Semua field harus diisi!";
        }
    } elseif ($action == 'edit') {
        // Update data pasien
        $id = $_POST['id'];
        $nama = $_POST['nama'];
        $alamat = $_POST['alamat'];
        $no_ktp = $_POST['no_ktp'];
        $no_hp = $_POST['no_hp'];
        $no_rm = generateNoRM($no_ktp); // Update nomor rekam medis berdasarkan no_ktp baru

        if (!empty($id) && !empty($nama) && !empty($alamat) && !empty($no_ktp) && !empty($no_hp)) {
            $stmt = $conn->prepare("UPDATE pasien SET nama=?, alamat=?, no_ktp=?, no_hp=?, no_rm=? WHERE id=?");
            $stmt->bind_param("sssssi", $nama, $alamat, $no_ktp, $no_hp, $no_rm, $id);
            if ($stmt->execute()) {
                echo "Data pasien berhasil diupdate.";
            } else {
                echo "Gagal mengupdate data pasien.";
            }
            $stmt->close();
        } else {
            echo "Semua field harus diisi!";
        }
    } elseif ($action == 'delete') {
        // Hapus data pasien
        $id = $_POST['id'];

        if (!empty($id)) {
            $stmt = $conn->prepare("DELETE FROM pasien WHERE id=?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "Data pasien berhasil dihapus.";
            } else {
                echo "Gagal menghapus data pasien.";
            }
            $stmt->close();
        } else {
            echo "ID tidak boleh kosong!";
        }
    }
    exit;
}

// Ambil data pasien
$sql = "SELECT * FROM pasien";
$result = $conn->query($sql);
$patients = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
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
            <h1>Manajemen Pasien</h1>
            <button class="btn btn-primary" onclick="showForm('add')">Tambah Pasien</button>
        </div>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Alamat</th>
                    <th>No KTP</th>
                    <th>No HP</th>
                    <th>No Rekam Medis</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($patients as $key => $patient): ?>
                <tr>
                    <td><?= $key + 1 ?></td>
                    <td><?= $patient['nama'] ?></td>
                    <td><?= $patient['alamat'] ?></td>
                    <td><?= $patient['no_ktp'] ?></td>
                    <td><?= $patient['no_hp'] ?></td>
                    <td><?= $patient['no_rm'] ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="showForm('edit', <?= $patient['id'] ?>)">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="deletePatient(<?= $patient['id'] ?>)">Hapus</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Form -->
    <div class="modal fade" id="patientModal" tabindex="-1" aria-labelledby="patientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="patientModalLabel">Tambah/Edit Pasien</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form">
                        <input type="hidden" name="id" id="patientId">
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
                            <label for="no_ktp" class="form-label">No KTP:</label>
                            <input type="text" class="form-control" name="no_ktp" id="no_ktp" required pattern="\d{16}" title="Nomor KTP harus terdiri dari 16 digit angka">
                        </div>
                        <div class="mb-3">
                            <label for="no_hp" class="form-label">No HP:</label>
                            <input type="text" class="form-control" name="no_hp" id="no_hp" required pattern="\d{10,15}" title="Nomor HP harus terdiri dari 10 hingga 15 digit angka">
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
                // Fetch data pasien untuk di-edit
                $.post('pasien.php', { action: 'fetch', id: id }, function(data) {
                    let patient = JSON.parse(data);
                    $('#patientId').val(patient.id);
                    $('#nama').val(patient.nama);
                    $('#alamat').val(patient.alamat);
                    $('#no_ktp').val(patient.no_ktp);
                    $('#no_hp').val(patient.no_hp);
                });
            }
            $('#patientModal').modal('show');
        }

        function deletePatient(id) {
            if (confirm('Yakin ingin menghapus data ini?')) {
                $.post('pasien.php', { action: 'delete', id: id }, function(response) {
                    alert(response);
                    location.reload();
                });
            }
        }

        $('#form').on('submit', function(e) {
            e.preventDefault();
            $.post('pasien.php', $(this).serialize(), function(response) {
                alert(response);
                location.reload();
            });
        });
    </script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
