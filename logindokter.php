<?php
    session_start();
// Konfigurasi koneksi ke database
    include('config/config.php');

    // Periksa apakah form telah disubmit
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Ambil data dari form
        $username = mysqli_real_escape_string($connect, $_POST['username']);
        $password = mysqli_real_escape_string($connect, $_POST['password']);

        // Query untuk memeriksa login
        $query = "SELECT * FROM dokter WHERE username = '$username' AND password = '$password'";
        $result = mysqli_query($connect, $query);

        if (mysqli_num_rows($result) == 1) {
            // Login berhasil
            $row = mysqli_fetch_assoc($result);
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['level'] = 'dokter'; // Tetapkan level dokter

            // Redirect ke halaman menu dokter
            header("Location: app/index.php");
        } else {
            // Login gagal, redirect ke halaman login dengan pesan error
            header("Location: logindokter.php?error=1");
        }
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Dokter</title>
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
        .error {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login Dokter</h2>
        <?php if (isset($_GET['error'])): ?>
            <p class="error">Username atau password salah!</p>
        <?php endif; ?>
        <form method="POST" action="logindokter.php">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
