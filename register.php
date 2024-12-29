<?php
session_start();
include 'koneksi.php'; // Hubungkan ke database

// Periksa apakah role dikirim melalui URL
if (isset($_GET['role'])) {
    $role = $_GET['role'];
    $_SESSION['selected_role'] = $role;
} else {
    header("Location: index.php");
    exit;
}

// Inisialisasi pesan error/sukses
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi input
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $message = "Semua field wajib diisi!";
    } elseif ($password !== $confirm_password) {
        $message = "Password dan konfirmasi password tidak cocok!";
    } else {
        if ($role === 'patient') {
            $name = trim($_POST['name']);
            $address = trim($_POST['address']);
            $ktp = trim($_POST['ktp']);
            $phone = trim($_POST['phone']);

            if (empty($name) || empty($address) || empty($ktp) || empty($phone)) {
                $message = "Semua data pasien wajib diisi!";
            } else {
                // Periksa apakah pasien sudah terdaftar berdasarkan No KTP
                $stmt = $koneksi->prepare("SELECT * FROM pasien WHERE no_ktp = ?");
                $stmt->bind_param("s", $ktp);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $message = "Pasien dengan No KTP ini sudah terdaftar!";
                } else {
                    // Generate No RM
                    $currentYearMonth = date("Ym");
                    $stmt = $koneksi->prepare("SELECT COUNT(*) AS count FROM pasien WHERE no_rm LIKE CONCAT(?, '%')");
                    $stmt->bind_param("s", $currentYearMonth);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $count = $result->fetch_assoc()['count'] + 1;
                    $stmt->close();

                    $no_rm = sprintf("%s-%03d", $currentYearMonth, $count);

                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Insert ke tabel users
                    $stmt = $koneksi->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $username, $hashed_password, $role);
                    $stmt->execute();
                    $user_id = $stmt->insert_id; // Ambil ID user
                    $stmt->close();

                    // Insert ke tabel pasien
                    $stmt = $koneksi->prepare("INSERT INTO pasien (nama, alamat, no_ktp, no_hp, no_rm, user_id) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssi", $name, $address, $ktp, $phone, $no_rm, $user_id);

                    if ($stmt->execute()) {
                        header("Location: login.php");
                        exit;
                    } else {
                        $message = "Pendaftaran gagal. Silakan coba lagi.";
                    }
                    $stmt->close();
                }
            }
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $koneksi->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashed_password, $role);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit;
            } else {
                $message = "Pendaftaran gagal. Silakan coba lagi.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Poliklinik</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
</head>
<body class="hold-transition register-page">
<div class="register-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="#" class="h1"><b>Poliklinik</b> Register</a>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Registrasi sebagai <b><?php echo ucfirst($role); ?></b></p>
            <?php if (!empty($message)): ?>
                <div class="alert alert-warning"><?php echo $message; ?></div>
            <?php endif; ?>
            <form action="register.php?role=<?php echo $role; ?>" method="post">
                <div class="input-group mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" name="confirm_password" class="form-control" placeholder="Konfirmasi Password" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <?php if ($role === 'patient'): ?>
                    <div class="input-group mb-3">
                        <input type="text" name="name" class="form-control" placeholder="Nama Lengkap" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-id-card"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" name="address" class="form-control" placeholder="Alamat" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-map-marker-alt"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" name="ktp" class="form-control" placeholder="Nomor KTP" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-id-card"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" name="phone" class="form-control" placeholder="Nomor HP" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-phone"></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="row">
                    <div class="col-8">
                        <a href="login.php" class="text-center">Sudah punya akun? Login di sini</a>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Register</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
