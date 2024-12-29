<?php
session_start();

// Periksa apakah user sudah login dan memiliki role doctor
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit;
}

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "karir");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Dapatkan id_dokter berdasarkan user_id dari sesi
$stmt = $conn->prepare("SELECT id FROM dokter WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$dokter = $result->fetch_assoc();
$stmt->close();

if (!$dokter) {
    die("Data dokter tidak ditemukan. Silakan periksa database Anda.");
}

$id_dokter = $dokter['id'];

// Proses input jadwal
$errors = [];
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['tambah_jadwal'])) {
    $hari = $_POST['hari'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];

    // Validasi jam
    if (strtotime($jam_mulai) >= strtotime($jam_selesai)) {
        $errors[] = "Jam mulai harus lebih awal daripada jam selesai.";
    }

    // Periksa apakah jadwal bertabrakan
    $stmt = $conn->prepare("
        SELECT * FROM jadwal_periksa 
        WHERE id_dokter = ? AND hari = ? AND 
        ( 
            (? BETWEEN jam_mulai AND jam_selesai) OR 
            (? BETWEEN jam_mulai AND jam_selesai) OR 
            (jam_mulai BETWEEN ? AND ?) OR 
            (jam_selesai BETWEEN ? AND ?)
        )
    ");
    $stmt->bind_param("isssssss", $id_dokter, $hari, $jam_mulai, $jam_selesai, $jam_mulai, $jam_selesai, $jam_mulai, $jam_selesai);
    $stmt->execute();
    $conflict = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if ($conflict) {
        $errors[] = "Jadwal ini bertabrakan dengan jadwal yang sudah ada.";
    }

    // Jika tidak ada error, tambahkan jadwal
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO jadwal_periksa (id_dokter, hari, jam_mulai, jam_selesai) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $id_dokter, $hari, $jam_mulai, $jam_selesai);
        if ($stmt->execute()) {
            header("Location: input_jadwal.php");
            exit;
        } else {
            $errors[] = "Gagal menambahkan jadwal. Silakan coba lagi.";
        }
        $stmt->close();
    }
}

// Proses hapus jadwal
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['hapus_jadwal'])) {
    $id_jadwal = $_POST['id_jadwal'];
    $stmt = $conn->prepare("DELETE FROM jadwal_periksa WHERE id = ? AND id_dokter = ?");
    $stmt->bind_param("ii", $id_jadwal, $id_dokter);
    $stmt->execute();
    $stmt->close();
    header("Location: input_jadwal.php");
    exit;
}

// Ambil jadwal periksa berdasarkan id_dokter
$stmt = $conn->prepare("
    SELECT id, hari, jam_mulai, jam_selesai 
    FROM jadwal_periksa 
    WHERE id_dokter = ?
    ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), jam_mulai
");
$stmt->bind_param("i", $id_dokter);
$stmt->execute();
$result = $stmt->get_result();
$jadwal_periksa = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Jadwal Periksa</title>
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
            <span class="brand-text font-weight-light">Dokter Panel</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
                    <li class="nav-item">
                        <a href="dokter_dashboard.php" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="input_jadwal.php" class="nav-link active">
                            <i class="nav-icon fas fa-calendar-alt"></i>
                            <p>Jadwal Periksa</p>
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
                <h1 class="m-0">Input Jadwal Periksa</h1>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">
                <!-- Form Tambah Jadwal -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Tambah Jadwal Periksa</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul>
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="form-group">
                                <label>Hari</label>
                                <select name="hari" class="form-control" required>
                                    <option value="">Pilih Hari</option>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                    <option value="Minggu">Minggu</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Jam Mulai</label>
                                <input type="time" name="jam_mulai" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Jam Selesai</label>
                                <input type="time" name="jam_selesai" class="form-control" required>
                            </div>
                            <button type="submit" name="tambah_jadwal" class="btn btn-primary">Tambah Jadwal</button>
                        </form>
                    </div>
                </div>

                <!-- Tabel Jadwal -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Jadwal Periksa Anda</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($jadwal_periksa)): ?>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Hari</th>
                                        <th>Jam Mulai</th>
                                        <th>Jam Selesai</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($jadwal_periksa as $index => $jadwal): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($jadwal['hari']); ?></td>
                                            <td><?php echo htmlspecialchars($jadwal['jam_mulai']); ?></td>
                                            <td><?php echo htmlspecialchars($jadwal['jam_selesai']); ?></td>
                                            <td>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="id_jadwal" value="<?php echo $jadwal['id']; ?>">
                                                    <button type="submit" name="hapus_jadwal" class="btn btn-danger btn-sm">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>Anda belum memiliki jadwal periksa.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
