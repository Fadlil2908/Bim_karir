<?php
session_start();

// Periksa apakah user sudah login dan memiliki role 'doctor'
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

// Ambil daftar pasien yang belum diperiksa
$query = "
    SELECT 
        dp.id AS id_daftar_poli, 
        p.nama AS nama_pasien, 
        dp.keluhan, 
        dp.no_antrian, 
        jp.hari, 
        jp.jam_mulai, 
        jp.jam_selesai
    FROM daftar_poli dp
    JOIN pasien p ON dp.id_pasien = p.id
    JOIN jadwal_periksa jp ON dp.id_jadwal = jp.id
    LEFT JOIN periksa pr ON dp.id = pr.id_daftar_poli
    WHERE jp.id_dokter = ? AND pr.id_daftar_poli IS NULL
    ORDER BY dp.no_antrian ASC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_dokter);
$stmt->execute();
$result = $stmt->get_result();
$daftar_pasien = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Ambil daftar obat
$query = "SELECT id, nama_obat, harga FROM obat";
$result = $conn->query($query);
$daftar_obat = $result->fetch_all(MYSQLI_ASSOC);

// Jika form disubmit, simpan hasil pemeriksaan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_daftar_poli = $_POST['id_daftar_poli'];
    $catatan = $_POST['catatan'];
    $selected_obat = $_POST['obat'] ?? [];
    $biaya_obat = 0;

    // Hitung total biaya obat berdasarkan obat yang dipilih
    foreach ($selected_obat as $id_obat) {
        $stmt = $conn->prepare("SELECT harga FROM obat WHERE id = ?");
        $stmt->bind_param("i", $id_obat);
        $stmt->execute();
        $result = $stmt->get_result();
        $obat = $result->fetch_assoc();
        $biaya_obat += $obat['harga'];
        $stmt->close();
    }

    $biaya_jasa_dokter = 150000;
    $biaya_periksa = $biaya_obat + $biaya_jasa_dokter;

    // Simpan data pemeriksaan ke tabel `periksa`
    $stmt = $conn->prepare("
        INSERT INTO periksa (id_daftar_poli, tgl_periksa, catatan, biaya_periksa) 
        VALUES (?, NOW(), ?, ?)
    ");
    $stmt->bind_param("isd", $id_daftar_poli, $catatan, $biaya_periksa);
    $stmt->execute();
    $id_periksa = $stmt->insert_id;
    $stmt->close();

    // Simpan data obat yang dipilih
    foreach ($selected_obat as $id_obat) {
        $stmt = $conn->prepare("INSERT INTO detail_periksa (id_periksa, id_obat) VALUES (?, ?)");
        $stmt->bind_param("ii", $id_periksa, $id_obat);
        $stmt->execute();
        $stmt->close();
    }

    echo "<script>alert('Data pemeriksaan berhasil disimpan.'); window.location.href = 'periksa_pasien.php';</script>";
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Periksa Pasien</title>
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
        <a href="dashboard.php" class="brand-link">
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
                        <a href="dokter_dashboard.php" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="periksa_pasien.php" class="nav-link active">
                            <i class="nav-icon fas fa-user-md"></i>
                            <p>Periksa Pasien</p>
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
                <h1 class="m-0">Periksa Pasien</h1>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <!-- Daftar Pasien -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Pasien</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($daftar_pasien): ?>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>No. Antrian</th>
                                        <th>Nama Pasien</th>
                                        <th>Keluhan</th>
                                        <th>Jadwal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($daftar_pasien as $row): ?>
                                        <tr>
                                            <td><?php echo $row['no_antrian']; ?></td>
                                            <td><?php echo $row['nama_pasien']; ?></td>
                                            <td><?php echo $row['keluhan']; ?></td>
                                            <td><?php echo $row['hari']; ?>, <?php echo $row['jam_mulai']; ?> - <?php echo $row['jam_selesai']; ?></td>
                                            <td>
                                                <button class="btn btn-primary btn-sm" onclick="isiForm('<?php echo $row['id_daftar_poli']; ?>', '<?php echo $row['nama_pasien']; ?>')">Periksa</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p>Tidak ada pasien yang menunggu.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Form Pemeriksaan -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Form Pemeriksaan</h3>
                    </div>
                    <div class="card-body">
                        <form action="periksa_pasien.php" method="post">
                            <input type="hidden" name="id_daftar_poli" id="id_daftar_poli" required>
                            <div class="form-group">
                                <label for="nama_pasien">Nama Pasien</label>
                                <input type="text" id="nama_pasien" class="form-control" readonly>
                            </div>
                            <div class="form-group">
                                <label for="catatan">Catatan</label>
                                <textarea name="catatan" id="catatan" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Obat</label><br>
                                <?php foreach ($daftar_obat as $obat): ?>
                                    <div>
                                        <input type="checkbox" name="obat[]" value="<?php echo $obat['id']; ?>" class="obat-checkbox" data-harga="<?php echo $obat['harga']; ?>">
                                        <?php echo $obat['nama_obat']; ?> - Rp <?php echo number_format($obat['harga'], 0, ',', '.'); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="submit" class="btn btn-success">Simpan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function isiForm(idDaftarPoli, namaPasien) {
    document.getElementById('id_daftar_poli').value = idDaftarPoli;
    document.getElementById('nama_pasien').value = namaPasien;
}
</script>
</body>
</html>
