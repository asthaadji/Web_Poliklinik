<?php
    session_start();
    include('config.php');

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validasi input kosong
    if ($username == '' || $password == '') {
        header('Location: ../index.php?error=2');
        exit();
    }

    // Query untuk login sebagai admin
    $query_admin = mysqli_query($connect, "SELECT * FROM tb_admin WHERE username = '$username' AND password = '$password'");
    if (mysqli_num_rows($query_admin) == 1) {
        $user = mysqli_fetch_array($query_admin);
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['level'] = 'admin';
        header('Location: ../app/index.php'); // Redirect ke halaman admin
        exit();
    }

    // Query untuk login sebagai pasien
    $query_pasien = mysqli_query($connect, "SELECT * FROM pasien WHERE username = '$username' AND password = '$password'");
    if (mysqli_num_rows($query_pasien) == 1) {
        $user = mysqli_fetch_array($query_pasien);
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['level'] = 'pasien';
        header('Location: ../menu/pasien'); // Redirect ke halaman pasien
        exit();
    }

    // Query untuk login sebagai dokter
    $query_dokter = mysqli_query($connect, "SELECT * FROM dokter WHERE username = '$username' AND password = '$password'");
    if (mysqli_num_rows($query_dokter) == 1) {
        $user = mysqli_fetch_array($query_dokter);
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['level'] = 'dokter';
        header('Location: ../menu/dokter'); // Redirect ke halaman dokter
        exit();
    }

    // Jika login gagal
    header('Location: ../index.php?error=1');
    exit();
?>
