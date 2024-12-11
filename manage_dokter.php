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

// Inisialisasi pesan
$message = "";

// Menambah data dokter
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_dokter'])) {
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $no_hp = $_POST['no_hp'];
    $id_poli = $_POST['id_poli'];

    $stmt = $conn->prepare("INSERT INTO dokter (nama, alamat, no_hp, id_poli) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $nama, $alamat, $no_hp, $id_poli);

    if ($stmt->execute()) {
        $message = "Data dokter berhasil ditambahkan.";
    } else {
        $message = "Gagal menambahkan data dokter: " . $conn->error;
    }
    $stmt->close();
}

// Menghapus data dokter
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    $stmt = $conn->prepare("DELETE FROM dokter WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $message = "Data dokter berhasil dihapus.";
    } else {
        $message = "Gagal menghapus data dokter: " . $conn->error;
    }
    $stmt->close();
}

// Mengambil data dokter
$dokter_result = $conn->query("SELECT dokter.id, dokter.nama, dokter.alamat, dokter.no_hp, poli.nama_poli 
                               FROM dokter 
                               LEFT JOIN poli ON dokter.id_poli = poli.id");

// Mengambil data poli untuk dropdown
$poli_result = $conn->query("SELECT id, nama_poli FROM poli");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Dokter</title>
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
                        <a href="manage_dokter.php" class="nav-link active">
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
                <h1 class="m-0">Kelola Data Dokter</h1>
                <?php if (!empty($message)): ?>
                    <div class="alert alert-info"><?php echo $message; ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <!-- Form Tambah Dokter -->
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Tambah Dokter</h3>
                    </div>
                    <form action="manage_dokter.php" method="post">
                        <div class="card-body">
                            <div class="form-group">
                                <label>Nama</label>
                                <input type="text" name="nama" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Alamat</label>
                                <input type="text" name="alamat" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>No HP</label>
                                <input type="text" name="no_hp" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Poli</label>
                                <select name="id_poli" class="form-control" required>
                                    <option value="">Pilih Poli</option>
                                    <?php while ($row = $poli_result->fetch_assoc()): ?>
                                        <option value="<?php echo $row['id']; ?>"><?php echo $row['nama_poli']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="add_dokter" class="btn btn-primary">Tambah Dokter</button>
                        </div>
                    </form>
                </div>

                <!-- Tabel Data Dokter -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Dokter</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Alamat</th>
                                    <th>No HP</th>
                                    <th>Poli</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $dokter_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['nama']; ?></td>
                                        <td><?php echo $row['alamat']; ?></td>
                                        <td><?php echo $row['no_hp']; ?></td>
                                        <td><?php echo $row['nama_poli']; ?></td>
                                        <td>
                                            <a href="edit_dokter.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="manage_dokter.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
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
