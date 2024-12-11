<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "karir");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Query untuk menghitung data
$query_poli = "SELECT COUNT(*) AS total_poli FROM poli";
$query_dokter = "SELECT COUNT(*) AS total_dokter FROM dokter";
$query_pasien = "SELECT COUNT(*) AS total_pasien FROM pasien";

$result_poli = $conn->query($query_poli)->fetch_assoc();
$result_dokter = $conn->query($query_dokter)->fetch_assoc();
$result_pasien = $conn->query($query_pasien)->fetch_assoc();

$total_poli = $result_poli['total_poli'];
$total_dokter = $result_dokter['total_dokter'];
$total_pasien = $result_pasien['total_pasien'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Poliklinik</title>
    <!-- AdminLTE & Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="logout.php" class="nav-link">Logout</a>
                </li>
            </ul>
        </nav>
        <!-- Sidebar -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="#" class="brand-link">
                <i class="fas fa-user-shield brand-image"></i>
                <span class="brand-text font-weight-light">Admin Dashboard</span>
            </a>
            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column">
                        <li class="nav-item">
                            <a href="#" class="nav-link active">
                                <i class="nav-icon fas fa-home"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manage_dokter.php" class="nav-link">
                                <i class="nav-icon fas fa-user-md"></i>
                                <p>Kelola Dokter</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manage_pasien.php" class="nav-link">
                                <i class="nav-icon fas fa-user-injured"></i>
                                <p>Kelola Pasien</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manage_obat.php" class="nav-link">
                                <i class="nav-icon fas fa-pills"></i>
                                <p>Kelola Obat</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="manage_poli.php" class="nav-link">
                                <i class="nav-icon fas fa-clinic-medical"></i>
                                <p>Kelola Poli</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>
        <!-- Content -->
        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <h1 class="m-0">Dashboard Admin</h1>
                </div>
            </div>
            <div class="content">
                <div class="container-fluid">
                    <!-- Statistik -->
                    <div class="row">
                        <div class="col-lg-4 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3><?php echo $total_poli; ?></h3>
                                    <p>Total Poli</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clinic-medical"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3><?php echo $total_dokter; ?></h3>
                                    <p>Total Dokter</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-md"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3><?php echo $total_pasien; ?></h3>
                                    <p>Total Pasien</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-injured"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Informasi tambahan -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Informasi Sistem</h5>
                                    <p class="card-text">Selamat datang di dashboard admin. Gunakan menu di sebelah kiri untuk mengelola data di sistem poliklinik.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AdminLTE & Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
