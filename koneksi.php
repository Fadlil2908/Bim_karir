<?php
$host = "localhost"; // Server host
$user = "root"; // Username MySQL
$pass = ""; // Password MySQL
$db = "karir2024"; // Nama database Anda

$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
