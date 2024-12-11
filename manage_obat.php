<?php
session_start();

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "karir");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add new "obat" data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_obat'])) {
    $nama_obat = $_POST['nama_obat'];
    $kemasan = $_POST['kemasan'];
    $harga = $_POST['harga'];

    $stmt = $conn->prepare("INSERT INTO obat (nama_obat, kemasan, harga) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $nama_obat, $kemasan, $harga);

    if ($stmt->execute()) {
        $message = "Obat berhasil ditambahkan.";
    } else {
        $error = "Gagal menambahkan obat: " . $conn->error;
    }
    $stmt->close();
}

// Delete "obat" data
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM obat WHERE id = ?");
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        $message = "Obat berhasil dihapus.";
    } else {
        $error = "Gagal menghapus obat: " . $conn->error;
    }
    $stmt->close();
}

// Fetch all "obat" data
$result = $conn->query("SELECT * FROM obat");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Obat</title>
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
                <ul class="nav nav-pills nav-sidebar flex-column">
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
                        <a href="manage_obat.php" class="nav-link active">
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
                <h1 class="m-0">Kelola Obat</h1>
                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <!-- Add New Obat Form -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Tambah Obat</h3>
                    </div>
                    <form action="" method="post">
                        <div class="card-body">
                            <div class="form-group">
                                <label>Nama Obat</label>
                                <input type="text" name="nama_obat" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Kemasan</label>
                                <input type="text" name="kemasan" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Harga</label>
                                <input type="number" name="harga" class="form-control" required>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="add_obat" class="btn btn-primary">Tambah</button>
                        </div>
                    </form>
                </div>

                <!-- Obat Table -->
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Obat</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Obat</th>
                                    <th>Kemasan</th>
                                    <th>Harga</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['nama_obat']; ?></td>
                                        <td><?php echo $row['kemasan']; ?></td>
                                        <td><?php echo $row['harga']; ?></td>
                                        <td>
                                            <a href="edit_obat.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus?');" class="btn btn-danger btn-sm">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
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
