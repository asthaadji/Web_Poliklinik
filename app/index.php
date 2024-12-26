<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
if(!$_SESSION['nama']){
  header('Location: ../index.php?session=expired');
}
include('header.php');
include('../config/config.php');
?>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  
  <!-- Navbar -->
  <?php include('navbar.php');
  ?>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <!-- <a href="index.php" class="brand-link">
      <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">Dashboard</span>
    </a> -->

    <!-- Sidebar -->
    <?php include ('sidebar.php'); ?>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- /.content-header -->

    <!-- Main content -->
    <?php
      if (isset ($_GET['page'])){
        if ($_GET['page'] == 'dashboard'){
          include ('dashboard.php');
        }
        else if($_GET['page'] == 'data-dokter'){
          include ('dokter.php');
        }
        else if($_GET['page'] == 'data-poli'){
          include ('poli.php');
        }
        else if($_GET['page'] == 'data-obat'){
          include ('obat.php');
        }
        else if($_GET['page'] == 'data-pasien'){
          include ('pasien.php');
        }
        else if($_GET['page'] == 'jadwal-periksa-admin'){
          include ('jadwalperiksaadmin.php');
        }
        else if($_GET['page'] == 'antrian-admin'){
          include ('antrianadmin.php');
        }
        else if($_GET['page'] == 'profile-dokter'){
          include ('dokter/profiledokter.php');
        }
        else if($_GET['page'] == 'jadwal-periksa'){
          include ('jadwalperiksa.php');
        }
        else if($_GET['page'] == 'daftarpoli'){
          include ('daftarpoli.php');
        }
        else if($_GET['page'] == 'periksadokter'){
          include ('periksadokter.php');
        }
        else if($_GET['page'] == 'riwayat-periksa'){
          include ('riwayatperiksa.php');
        }
        else if($_GET['page'] == 'hasil-periksa'){
          include ('hasilperiksa.php');
        }
      }
    else{
      include ('dashboard.php');
    }
    ?>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <?php include ('footer.php') ?>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

</body>
</html>
