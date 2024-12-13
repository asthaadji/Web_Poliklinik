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

  <!-- Preloader -->
  

  <!-- Navbar -->
  <?php include('navbar.php');
  ?>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index.php" class="brand-link">
      <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">Dashboard</span>
    </a>

    <!-- Sidebar -->
    <?php include ('sidebar.php'); ?>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
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
