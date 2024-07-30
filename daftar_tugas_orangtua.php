<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['peran'] !== 'orangtua') {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nilai_siswa";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$mapel = isset($_GET['mapel']) ? $_GET['mapel'] : '';
$tugas = [];

// Debugging: Cek apakah $mapel sudah terisi dengan benar
if (empty($mapel)) {
    die("Parameter mapel tidak ada atau kosong.");
}

// Ambil ID Siswa berdasarkan ANAKSISWA dari data_user
$orangtua_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT ANAKSISWA FROM data_user WHERE ID = ?");
$stmt->bind_param("i", $orangtua_id);
$stmt->execute();
$stmt->bind_result($anak_siswa);
$stmt->fetch();
$stmt->close();

if ($anak_siswa) {
    // Ambil SISWA_ID dari data_user
    $stmt = $conn->prepare("SELECT ID FROM data_user WHERE NAMALENGKAP = ?");
    $stmt->bind_param("s", $anak_siswa);
    $stmt->execute();
    $stmt->bind_result($siswa_id);
    $stmt->fetch();
    $stmt->close();

    // Debugging: Cek apakah $siswa_id valid
    if (!$siswa_id) {
        die("ID siswa tidak ditemukan.");
    }

    // Ambil tugas berdasarkan mata pelajaran dan SISWA_ID
    $stmt = $conn->prepare("
        SELECT tugas.JUDUL_TUGAS, tugas.TANGGAL_TUGAS, tugas.DESKRIPSI, 
               COALESCE(nilai_siswa.NILAI, 'Belum dinilai') AS NILAI 
        FROM tugas 
        LEFT JOIN nilai_siswa 
        ON tugas.ID_TUGAS = nilai_siswa.ID_TUGAS AND nilai_siswa.SISWA_ID = ?
        WHERE tugas.MAPEL = ?
    ");
    $stmt->bind_param("is", $siswa_id, $mapel);
    $stmt->execute();
    $stmt->bind_result($judul_tugas, $tanggal_tugas, $deskripsi, $nilai);

    while ($stmt->fetch()) {
        // Format tanggal dengan hari dalam bahasa Indonesia
        $formatted_date = format_date_indonesia($tanggal_tugas);
        $tugas[] = ['judul_tugas' => $judul_tugas, 'tanggal_tugas' => $formatted_date, 'deskripsi' => $deskripsi, 'nilai' => $nilai];
    }

    $stmt->close();
}

$conn->close();

// Fungsi untuk mengubah tanggal ke format bahasa Indonesia
function format_date_indonesia($date) {
    $hari = array("Sun" => "Minggu", "Mon" => "Senin", "Tue" => "Selasa", "Wed" => "Rabu", "Thu" => "Kamis", "Fri" => "Jumat", "Sat" => "Sabtu");
    $bulan = array("01" => "Januari", "02" => "Februari", "03" => "Maret", "04" => "April", "05" => "Mei", "06" => "Juni", "07" => "Juli", "08" => "Agustus", "09" => "September", "10" => "Oktober", "11" => "November", "12" => "Desember");

    $timestamp = strtotime($date);
    $hari_indonesia = $hari[date('D', $timestamp)];
    $bulan_indonesia = $bulan[date('m', $timestamp)];
    $tanggal = date('d', $timestamp);
    $tahun = date('Y', $timestamp);

    return "$hari_indonesia, $tanggal $bulan_indonesia $tahun";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tugas Orangtua</title>
    <link rel="stylesheet" href="daftar_tugas_orangtua.css">
</head>
<body>
    <div class="logo-top-left">
        <img src="logobadag.png" alt="Logo">
    </div>

    <div class="page-title"><?php echo ucfirst(str_replace('_', ' ', $mapel)); ?></div>

    <div class="tasks-container">
        <table>
            <thead>
                <tr>
                    <th>Judul Tugas</th>
                    <th>Tanggal</th>
                    <th>Deskripsi</th>
                    <th>Nilai</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tugas)): ?>
                <tr>
                    <td colspan="4">Tidak ada tugas yang tersedia untuk mata pelajaran ini.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($tugas as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['judul_tugas']); ?></td>
                    <td><?php echo htmlspecialchars($item['tanggal_tugas']); ?></td>
                    <td><?php echo htmlspecialchars($item['deskripsi']); ?></td>
                    <td><?php echo htmlspecialchars($item['nilai']); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a class="back" href="orangtua.php">Kembali</a>
</body>
</html>
