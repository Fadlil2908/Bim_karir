<?php
session_start();

// Periksa apakah user sudah login dan memiliki role dokter
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit;
}

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "karir");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil ID dokter berdasarkan user_id
$stmt = $conn->prepare("SELECT id FROM dokter WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$dokter = $result->fetch_assoc();
$stmt->close();

if (!$dokter) {
    echo "<script>alert('Data dokter tidak ditemukan!'); window.history.back();</script>";
    exit;
}

$id_dokter = $dokter['id'];

// Ambil data pasien yang pernah diperiksa oleh dokter ini
$query = "
    SELECT DISTINCT 
        p.id AS id_pasien,
        p.nama AS nama_pasien,
        p.alamat,
        p.no_ktp,
        p.no_hp,
        p.no_rm
    FROM periksa pr
    JOIN daftar_poli dp ON pr.id_daftar_poli = dp.id
    JOIN pasien p ON dp.id_pasien = p.id
    JOIN jadwal_periksa jp ON dp.id_jadwal = jp.id
    JOIN dokter d ON jp.id_dokter = d.id
    WHERE d.id = ?
    ORDER BY p.nama ASC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_dokter);
$stmt->execute();
$result = $stmt->get_result();
$pasien = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pasien</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                        <a href="riwayat_pasien.php" class="nav-link active">
                            <i class="nav-icon fas fa-file-medical"></i>
                            <p>Riwayat Pasien</p>
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
                <h1 class="m-0">Riwayat Pasien</h1>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Pasien</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($pasien): ?>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>Alamat</th>
                                        <th>No. KTP</th>
                                        <th>No. HP</th>
                                        <th>No. RM</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pasien as $index => $row): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_pasien']); ?></td>
                                            <td><?php echo htmlspecialchars($row['alamat']); ?></td>
                                            <td><?php echo htmlspecialchars($row['no_ktp']); ?></td>
                                            <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
                                            <td><?php echo htmlspecialchars($row['no_rm']); ?></td>
                                            <td>
                                                <button class="btn btn-info btn-sm" onclick="lihatDetailRiwayat(<?php echo $row['id_pasien']; ?>)">
                                                    Detail Riwayat
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>Belum ada pasien yang tercatat dalam riwayat pemeriksaan.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk Detail Riwayat -->
<div class="modal fade" id="detailRiwayatModal" tabindex="-1" aria-labelledby="detailRiwayatLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailRiwayatLabel">Detail Riwayat Pasien</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalRiwayatContent">
                <!-- Konten akan dimuat via AJAX -->
                <p>Memuat data...</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function lihatDetailRiwayat(idPasien) {
    const modal = new bootstrap.Modal(document.getElementById('detailRiwayatModal'));
    modal.show();

    document.getElementById('modalRiwayatContent').innerHTML = '<p>Memuat data...</p>';

    fetch('get_detail_riwayat.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id_pasien=' + idPasien,
    })
        .then(response => response.text())
        .then(data => {
            document.getElementById('modalRiwayatContent').innerHTML = data;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('modalRiwayatContent').innerHTML = '<p>Terjadi kesalahan saat memuat data.</p>';
        });
}
</script>
</body>
</html>
