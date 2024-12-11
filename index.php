<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di Poliklinik</title>
    <!-- AdminLTE & Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
</head>
<body class="hold-transition login-page" style="background-color: #e3f2fd;">
<div class="container text-center">
    <h1 class="display-4 mt-5 mb-3">Selamat Datang di Poliklinik</h1>
    <p class="lead">Silakan pilih tindakan Anda sebagai pasien, dokter, atau admin.</p>

    <div class="row justify-content-center">
        <!-- Tombol Register -->
        <div class="col-md-4 mb-3">
            <a href="register.php?role=patient" class="btn btn-primary btn-lg btn-block">
                <i class="fas fa-user-injured"></i> Daftar Sebagai Pasien
            </a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="register.php?role=doctor" class="btn btn-primary btn-lg btn-block">
                <i class="fas fa-user-md"></i> Daftar Sebagai Dokter
            </a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="register.php?role=admin" class="btn btn-primary btn-lg btn-block">
                <i class="fas fa-user-shield"></i> Daftar Sebagai Admin
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <!-- Tombol Login -->
        <div class="col-md-4 mb-3">
            <a href="login.php?role=patient" class="btn btn-success btn-lg btn-block">
                <i class="fas fa-sign-in-alt"></i> Login Sebagai Pasien
            </a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="login.php?role=doctor" class="btn btn-success btn-lg btn-block">
                <i class="fas fa-sign-in-alt"></i> Login Sebagai Dokter
            </a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="login.php?role=admin" class="btn btn-success btn-lg btn-block">
                <i class="fas fa-sign-in-alt"></i> Login Sebagai Admin
            </a>
        </div>
    </div>
</div>

<!-- AdminLTE & Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
