<?php
session_start();

// Periksa apakah user sudah login dan memiliki role patient
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit;
}

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "karir");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil ID pasien berdasarkan user_id
$stmt = $conn->prepare("SELECT id FROM pasien WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$pasien = $result->fetch_assoc();
$stmt->close();

if (!$pasien) {
    echo "<script>alert('Data pasien tidak ditemukan!'); window.history.back();</script>";
    exit;
}

$id_pasien = $pasien['id'];

// Jika form disubmit, proses data pendaftaran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_jadwal = $_POST['id_jadwal'];
    $keluhan = $_POST['keluhan'];

    // Periksa apakah jadwal sudah digunakan oleh pasien lain yang belum diperiksa
    $stmt = $conn->prepare("
        SELECT dp.id 
        FROM daftar_poli dp
        LEFT JOIN periksa pr ON dp.id = pr.id_daftar_poli
        WHERE dp.id_jadwal = ? AND pr.id IS NULL
    ");
    $stmt->bind_param("i", $id_jadwal);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "<script>alert('Jadwal ini sedang digunakan oleh pasien lain dan belum selesai diperiksa.'); window.history.back();</script>";
        exit;
    }
    $stmt->close();

    // Ambil nomor antrian terbaru dari tabel `daftar_poli`
    $stmt = $conn->prepare("SELECT MAX(no_antrian) AS max_antrian FROM daftar_poli WHERE id_jadwal = ?");
    $stmt->bind_param("i", $id_jadwal);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $no_antrian = $row['max_antrian'] ? $row['max_antrian'] + 1 : 1;
    $stmt->close();

    // Insert data ke tabel `daftar_poli`
    $stmt = $conn->prepare("INSERT INTO daftar_poli (id_pasien, id_jadwal, keluhan, no_antrian) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisi", $id_pasien, $id_jadwal, $keluhan, $no_antrian);

    if ($stmt->execute()) {
        echo "<script>alert('Pendaftaran berhasil. Nomor antrian Anda: $no_antrian'); window.location.href = 'daftar_poli.php';</script>";
    } else {
        echo "<script>alert('Pendaftaran gagal: " . $stmt->error . "'); window.history.back();</script>";
    }

    $stmt->close();
}

// Ambil daftar jadwal, dokter, dan poli
$query = "
    SELECT jp.id AS id_jadwal, d.nama AS nama_dokter, p.nama_poli, jp.jam_mulai, jp.jam_selesai, jp.hari
    FROM jadwal_periksa jp
    JOIN dokter d ON jp.id_dokter = d.id
    JOIN poli p ON d.id_poli = p.id
";
$result = $conn->query($query);
$jadwal = $result->fetch_all(MYSQLI_ASSOC);

// Ambil daftar poli yang sudah didaftarkan oleh pasien ini
$query = "
    SELECT 
        dp.id AS id_daftar, 
        p.nama_poli, 
        d.nama AS nama_dokter, 
        jp.hari, 
        jp.jam_mulai, 
        jp.jam_selesai, 
        dp.keluhan, 
        dp.no_antrian,
        IF(pr.biaya_periksa IS NOT NULL, CONCAT('Rp ', FORMAT(pr.biaya_periksa, 0)), 'Belum diperiksa') AS status_biaya
    FROM daftar_poli dp
    JOIN jadwal_periksa jp ON dp.id_jadwal = jp.id
    JOIN dokter d ON jp.id_dokter = d.id
    JOIN poli p ON d.id_poli = p.id
    LEFT JOIN periksa pr ON dp.id = pr.id_daftar_poli
    WHERE dp.id_pasien = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_pasien);
$stmt->execute();
$result = $stmt->get_result();
$daftar_poli = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Poli</title>
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
            <span class="brand-text font-weight-light">Sistem Karir</span>
        </a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="info">
                    <a href="#" class="d-block"><?php echo $_SESSION['username']; ?></a>
                </div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="daftar_poli.php" class="nav-link active">
                            <i class="nav-icon fas fa-clinic-medical"></i>
                            <p>Daftar Poli</p>
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
                <h1 class="m-0">Daftar Poli</h1>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Pilih Poli dan Dokter</h3>
                    </div>
                    <div class="card-body">
                        <form action="daftar_poli.php" method="post">
                            <div class="form-group">
                                <label for="id_jadwal">Pilih Jadwal</label>
                                <select name="id_jadwal" id="id_jadwal" class="form-control" required>
                                    <option value="" disabled selected>-- Pilih Jadwal --</option>
                                    <?php foreach ($jadwal as $row): ?>
                                        <option value="<?php echo $row['id_jadwal']; ?>">
                                            <?php echo $row['nama_poli']; ?> - <?php echo $row['nama_dokter']; ?> (<?php echo $row['hari']; ?>, <?php echo $row['jam_mulai']; ?> - <?php echo $row['jam_selesai']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="keluhan">Keluhan</label>
                                <textarea name="keluhan" id="keluhan" class="form-control" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Daftar</button>
                        </form>
                    </div>
                </div>

                <!-- Daftar Poli -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Poli Anda</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($daftar_poli): ?>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Poli</th>
                                        <th>Dokter</th>
                                        <th>Jadwal</th>
                                        <th>Keluhan</th>
                                        <th>No. Antrian</th>
                                        <th>Status Biaya</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($daftar_poli as $row): ?>
                                        <tr>
                                            <td><?php echo $row['nama_poli']; ?></td>
                                            <td><?php echo $row['nama_dokter']; ?></td>
                                            <td><?php echo $row['hari']; ?>, <?php echo $row['jam_mulai']; ?> - <?php echo $row['jam_selesai']; ?></td>
                                            <td><?php echo $row['keluhan']; ?></td>
                                            <td><?php echo $row['no_antrian']; ?></td>
                                            <td><?php echo $row['status_biaya']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>Belum ada pendaftaran.</p>
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
