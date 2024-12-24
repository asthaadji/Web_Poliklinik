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

// Ambil data jumlah dokter
$sql_dokter = "SELECT COUNT(*) as total FROM dokter";
$result_dokter = $conn->query($sql_dokter);
$total_dokter = $result_dokter->fetch_assoc()['total'];

// Ambil data jumlah pasien
$sql_pasien = "SELECT COUNT(*) as total FROM pasien";
$result_pasien = $conn->query($sql_pasien);
$total_pasien = $result_pasien->fetch_assoc()['total'];

// Ambil data jumlah poli
$sql_poli = "SELECT COUNT(*) as total FROM poli";
$result_poli = $conn->query($sql_poli);
$total_poli = $result_poli->fetch_assoc()['total'];

// Ambil data jumlah obat
$sql_obat = "SELECT COUNT(*) as total FROM obat";
$result_obat = $conn->query($sql_obat);
$total_obat = $result_obat->fetch_assoc()['total'];
?>
<div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Dashboard Poliklinik Mitra Sehat</h1>
          </div>
          <!-- /.col -->
          <div class="col-sm-6">
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
<section class="content">
    <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?= $total_dokter; ?></h3>
                        <p>Dokter</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-md"></i> <!-- Ikon dokter -->
                    </div>
                    <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3><?= $total_pasien; ?></h3>
                        <p>Pasien</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i> <!-- Ikon pasien -->
                    </div>
                    <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?= $total_poli; ?></h3>
                        <p>Poli</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clinic-medical"></i> <!-- Ikon poli -->
                    </div>
                    <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3><?= $total_obat; ?></h3>
                        <p>Obat</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-pills"></i> <!-- Ikon obat -->
                    </div>
                    <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
        </div>
        <!-- /.row -->
    </div>
</section>

