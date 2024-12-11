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

// Periksa apakah ID dokter diberikan
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_dokter.php");
    exit;
}

$edit_id = $_GET['id'];

// Ambil data dokter berdasarkan ID
$result = $conn->query("SELECT * FROM dokter WHERE id = $edit_id");
if ($result->num_rows == 0) {
    header("Location: manage_dokter.php");
    exit;
}

$edit_data = $result->fetch_assoc();

// Proses update data dokter
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_dokter'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $no_hp = $_POST['no_hp'];
    $id_poli = $_POST['id_poli'];

    $stmt = $conn->prepare("UPDATE dokter SET nama = ?, alamat = ?, no_hp = ?, id_poli = ? WHERE id = ?");
    $stmt->bind_param("sssii", $nama, $alamat, $no_hp, $id_poli, $id);

    if ($stmt->execute()) {
        $message = "Data dokter berhasil diperbarui.";
        header("Location: manage_dokter.php?message=" . urlencode($message));
        exit;
    } else {
        $message = "Gagal memperbarui data dokter: " . $conn->error;
    }
    $stmt->close();
}

// Ambil daftar poli untuk dropdown
$poli_result = $conn->query("SELECT id, nama_poli FROM poli");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Dokter</title>
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

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">Edit Dokter</h1>
                <?php if (!empty($message)): ?>
                    <div class="alert alert-info"><?php echo $message; ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <!-- Form Edit Dokter -->
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Form Edit Dokter</h3>
                    </div>
                    <form action="edit_dokter.php?id=<?php echo $edit_id; ?>" method="post">
                        <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                        <div class="card-body">
                            <div class="form-group">
                                <label>Nama</label>
                                <input type="text" name="nama" class="form-control" value="<?php echo $edit_data['nama']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Alamat</label>
                                <input type="text" name="alamat" class="form-control" value="<?php echo $edit_data['alamat']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>No HP</label>
                                <input type="text" name="no_hp" class="form-control" value="<?php echo $edit_data['no_hp']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Poli</label>
                                <select name="id_poli" class="form-control" required>
                                    <?php while ($row = $poli_result->fetch_assoc()): ?>
                                        <option value="<?php echo $row['id']; ?>" <?php echo $row['id'] == $edit_data['id_poli'] ? "selected" : ""; ?>>
                                            <?php echo $row['nama_poli']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="update_dokter" class="btn btn-warning">Simpan Perubahan</button>
                            <a href="manage_dokter.php" class="btn btn-secondary">Kembali</a>
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
