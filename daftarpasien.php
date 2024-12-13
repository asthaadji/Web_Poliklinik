<?php
    // Mulai sesi
    session_start();

    // Konfigurasi koneksi ke database
    include('config/config.php');

    // Periksa apakah form telah disubmit
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Ambil data dari form
        $nama = mysqli_real_escape_string($connect, $_POST['nama']);
        $alamat = mysqli_real_escape_string($connect, $_POST['alamat']);
        $no_ktp = mysqli_real_escape_string($connect, $_POST['no_ktp']);
        $no_hp = mysqli_real_escape_string($connect, $_POST['no_hp']);

        // Set username dan password
        $username = $nama; // username sama dengan nama
        $password = $alamat; // password sama dengan alamat

        // Generate default no_rm
        $ktp_awal = substr($no_ktp, 0, 4); // 4 angka awal KTP
        $ktp_akhir = substr($no_ktp, -2); // 2 angka terakhir KTP
        $no_rm = "RM-" . $ktp_awal . "-" . $ktp_akhir;

        // Query untuk memasukkan data ke tabel pasien
        $query = "INSERT INTO pasien (nama, alamat, no_ktp, no_hp, no_rm, username, password, level) 
                  VALUES ('$nama', '$alamat', '$no_ktp', '$no_hp', '$no_rm', '$username', '$password', 'pasien')";

        if (mysqli_query($connect, $query)) {
            // Jika berhasil, redirect ke halaman sukses
            header("Location: index.php");
        } else {
            // Jika gagal, tampilkan pesan error
            echo "Error: " . $query . "<br>" . mysqli_error($connect);
        }
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Pasien</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
        }
        .container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        form label {
            margin-bottom: 5px;
            font-weight: bold;
        }
        form input {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        form button {
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        form button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Registrasi Pasien Baru</h2>
        <form method="POST" action="daftarpasien.php">
            <label for="nama">Nama</label>
            <input type="text" id="nama" name="nama" required>

            <label for="alamat">Alamat</label>
            <input type="text" id="alamat" name="alamat" required>

            <label for="no_ktp">Nomor KTP</label>
            <input type="text" id="no_ktp" name="no_ktp" required>

            <label for="no_hp">Nomor HP</label>
            <input type="text" id="no_hp" name="no_hp" required>

            <button type="submit">Daftar</button>
        </form>
    </div>
</body>
</html>
