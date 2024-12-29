<?php
session_start();

// Periksa apakah user sudah login dan memiliki role dokter
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    http_response_code(403);
    echo "Anda tidak memiliki akses ke halaman ini.";
    exit;
}

// Periksa parameter ID pasien
if (!isset($_POST['id_pasien'])) {
    http_response_code(400);
    echo "ID pasien tidak valid.";
    exit;
}

$id_pasien = (int) $_POST['id_pasien'];

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "karir");
if ($conn->connect_error) {
    http_response_code(500);
    echo "Koneksi ke database gagal.";
    exit;
}

// Ambil riwayat pemeriksaan pasien berdasarkan ID pasien
$query = "
    SELECT 
        pr.tgl_periksa,
        pr.catatan,
        pr.biaya_periksa,
        GROUP_CONCAT(ob.nama_obat SEPARATOR ', ') AS obat_diberikan
    FROM periksa pr
    JOIN daftar_poli dp ON pr.id_daftar_poli = dp.id
    LEFT JOIN detail_periksa det ON pr.id = det.id_periksa
    LEFT JOIN obat ob ON det.id_obat = ob.id
    WHERE dp.id_pasien = ?
    GROUP BY pr.id
    ORDER BY pr.tgl_periksa DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_pasien);
$stmt->execute();
$result = $stmt->get_result();
$riwayat = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// Tampilkan data dalam format HTML
if ($riwayat) {
    echo '<table class="table table-bordered">';
    echo '<thead>
            <tr>
                <th>Tanggal Periksa</th>
                <th>Catatan</th>
                <th>Biaya Periksa</th>
                <th>Obat Diberikan</th>
            </tr>
          </thead>';
    echo '<tbody>';
    foreach ($riwayat as $row) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['tgl_periksa']) . '</td>';
        echo '<td>' . htmlspecialchars($row['catatan']) . '</td>';
        echo '<td>Rp ' . number_format($row['biaya_periksa'], 0, ',', '.') . '</td>';
        echo '<td>' . htmlspecialchars($row['obat_diberikan'] ?: 'Tidak ada') . '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
} else {
    echo '<p>Pasien ini belum memiliki riwayat pemeriksaan.</p>';
}
?>
