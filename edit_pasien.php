<?php
session_start();

// Periksa apakah user sudah login sebagai admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "karir");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data pasien berdasarkan ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $conn->query("SELECT * FROM pasien WHERE id = $id");
    if ($result->num_rows > 0) {
        $pasien = $result->fetch_assoc();
    } else {
        echo "Data pasien tidak ditemukan.";
        exit;
    }
}

// Update data pasien
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $no_rm = $_POST['no_rm'];
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $no_ktp = $_POST['no_ktp'];
    $no_hp = $_POST['no_hp'];

    $stmt = $conn->prepare("UPDATE pasien SET no_rm = ?, nama = ?, alamat = ?, no_ktp = ?, no_hp = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $no_rm, $nama, $alamat, $no_ktp, $no_hp, $id);

    if ($stmt->execute()) {
        $message = "Data pasien berhasil diperbarui.";
        header("Location: manage_pasien.php?message=" . urlencode($message));
        exit;
    } else {
        $message = "Gagal memperbarui data pasien: " . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pasien</title>
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
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a href="logout.php" class="nav-link">Logout</a>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="#" class="brand-link">
            <span class="brand-text font-weight-light">Admin Panel</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
                    <li class="nav-item">
                        <a href="admin_dashboard.php" class="nav-link">
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
                        <a href="manage_pasien.php" class="nav-link active">
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

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">Edit Pasien</h1>
                <?php if (!empty($message)): ?>
                    <div class="alert alert-info"><?php echo $message; ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <!-- Form Edit Pasien -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Form Edit Pasien</h3>
                    </div>
                    <form action="" method="post">
                        <div class="card-body">
                            <div class="form-group">
                                <label>No RM</label>
                                <input type="text" name="no_rm" class="form-control" value="<?php echo $pasien['no_rm']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Nama</label>
                                <input type="text" name="nama" class="form-control" value="<?php echo $pasien['nama']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Alamat</label>
                                <input type="text" name="alamat" class="form-control" value="<?php echo $pasien['alamat']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>No KTP</label>
                                <input type="text" name="no_ktp" class="form-control" value="<?php echo $pasien['no_ktp']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>No HP</label>
                                <input type="text" name="no_hp" class="form-control" value="<?php echo $pasien['no_hp']; ?>" required>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            <a href="manage_pasien.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AdminLTE & Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
