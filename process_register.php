<?php
include 'koneksi.php'; // Hubungkan ke database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $query = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Pendaftaran berhasil! Silakan login.'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Pendaftaran gagal: " . mysqli_error($koneksi) . "'); window.history.back();</script>";
    }
}
?>
