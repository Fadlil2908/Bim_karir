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

// Ambil data dokter berdasarkan user ID yang login
$stmt = $conn->prepare("
    SELECT dokter.nama, dokter.alamat, dokter.no_hp, dokter.id_poli, poli.nama_poli 
    FROM dokter 
    LEFT JOIN poli ON dokter.id_poli = poli.id 
    WHERE dokter.user_id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$dokter = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Ambil daftar poli untuk dropdown
$poli_result = $conn->query("SELECT id, nama_poli FROM poli");
$polis = $poli_result->fetch_all(MYSQLI_ASSOC);

// Proses form submit
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $no_hp = $_POST['no_hp'];
    $id_poli = $_POST['id_poli'];
    $new_password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Update data dokter
    $stmt = $conn->prepare("UPDATE dokter SET nama = ?, alamat = ?, no_hp = ?, id_poli = ? WHERE user_id = ?");
    $stmt->bind_param("sssii", $nama, $alamat, $no_hp, $id_poli, $_SESSION['user_id']);
    if ($stmt->execute()) {
        $message = "Profil berhasil diperbarui.";
    } else {
        $message = "Gagal memperbarui profil: " . $conn->error;
    }
    $stmt->close();

    // Update password jika diisi
    if ($new_password) {
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_password, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $message .= " Password berhasil diperbarui.";
        } else {
            $message .= " Gagal memperbarui password: " . $conn->error;
        }
        $stmt->close();
    }

    // Reload data dokter
    header("Location: update_dokter.php");
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Dokter</title>
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
                        <a href="update_dokter.php" class="nav-link active">
                            <i class="nav-icon fas fa-user-edit"></i>
                            <p>Profil Dokter</p>
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
                <h1 class="m-0">Profil Dokter</h1>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                <?php if ($message): ?>
                    <div class="alert alert-info"><?php echo $message; ?></div>
                <?php endif; ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Perbarui Data Diri</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="update_dokter.php">
                            <div class="form-group">
                                <label>Nama</label>
                                <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($dokter['nama']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Alamat</label>
                                <input type="text" name="alamat" class="form-control" value="<?php echo htmlspecialchars($dokter['alamat']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>No HP</label>
                                <input type="text" name="no_hp" class="form-control" value="<?php echo htmlspecialchars($dokter['no_hp']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Poli</label>
                                <select name="id_poli" class="form-control" required>
                                    <?php foreach ($polis as $poli): ?>
                                        <option value="<?php echo $poli['id']; ?>" <?php if ($poli['id'] == $dokter['id_poli']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($poli['nama_poli']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Password Baru (Opsional)</label>
                                <input type="password" name="password" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
