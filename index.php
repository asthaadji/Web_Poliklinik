


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Poliklinik Mitra Sehat</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        header {
            background-color: #007BFF;
            color: white;
            padding: 20px;
            text-align: center;
        }
        header h1 {
            margin: 0;
            font-size: 2.5rem;
        }
        .content {
            display: flex;
            justify-content: center;
            align-items: center;
            flex: 1;
            gap: 30px;
            padding: 20px;
        }
        .content .card {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 300px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .content .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        .content .card h3 {
            margin-bottom: 20px;
            font-size: 1.5rem;
            color: #333;
        }
        .content .card p {
            margin-bottom: 20px;
            color: #555;
        }
        .content .card .buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .content .card a {
            text-decoration: none;
            background-color: #28a745;
            color: white;
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 16px;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        .contents {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .contents a {
            text-decoration: none;
            background-color:rgb(167, 40, 40);
            color: white;
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 16px;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        .content .card a:hover {
            background-color: #218838;
        }
        footer {
            background-color: #007BFF;
            color: white;
            text-align: center;
            padding: 10px;
        }
        footer p {
            margin: 0;
        }
    </style>
</head>
<body>
    <header>
        <h1>Poliklinik Mitra Sehat</h1>
    </header>

    <div class="content">
        <div class="card" id="login-pasien">
            <h3>Login dan Daftar Pasien</h3>
            <p>Untuk pasien baru atau yang sudah terdaftar, Anda bisa memilih untuk login atau daftar di sini.</p>
            <div class="buttons">
                <a href="loginpasien.php">Login Pasien</a>
                <a href="daftarpasien.php">Daftar Pasien</a>
            </div>
        </div>
        <div class="card" id="login-dokter">
            <h3>Login Dokter</h3>
            <p>Dokter yang ingin mengakses sistem, silakan login di sini.</p>
            <a href="logindokter.php">Login Dokter</a>
        </div>
    </div>
    <div class="contents">
        <div class="buttons">
            <a href="loginadmin.php">Login Admin</a>
        </div>
    </div><br> -->

    <footer>
        <p>&copy; 2024 Poliklinik Mitra Sehat</p>
    </footer>
</body>
</html>
