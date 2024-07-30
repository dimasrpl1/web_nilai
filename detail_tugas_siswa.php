<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['peran'] !== 'siswa') {
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

// Ambil ID tugas dari URL
$id_tugas = isset($_GET['id_tugas']) ? intval($_GET['id_tugas']) : 0;

// Ambil detail tugas
$tugas = null;
$stmt = $conn->prepare("SELECT JUDUL_TUGAS, DESKRIPSI, TENGGAT_WAKTU, MAPEL 
                        FROM tugas 
                        WHERE ID_TUGAS = ?");
$stmt->bind_param("i", $id_tugas);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $tugas = $result->fetch_assoc();
}
$stmt->close();

// Ambil nilai siswa
$nilai = null;
$stmt = $conn->prepare("SELECT NILAI FROM nilai_siswa WHERE ID_TUGAS = ? AND SISWA_ID = ?");
$stmt->bind_param("ii", $id_tugas, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $nilai = $result->fetch_assoc();
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tugas</title>
</head>
<body>
    <h1>Detail Tugas</h1>

    <?php if ($tugas): ?>
        <h2><?php echo htmlspecialchars($tugas['JUDUL_TUGAS']); ?></h2>
        <p><strong>Deskripsi:</strong> <?php echo htmlspecialchars($tugas['DESKRIPSI']); ?></p>
        <p><strong>Tenggat Waktu:</strong> <?php echo htmlspecialchars($tugas['TENGGAT_WAKTU']); ?></p>
        <p><strong>Mata Pelajaran:</strong> <?php echo htmlspecialchars($tugas['MAPEL']); ?></p>

        <h3>Nilai Anda:</h3>
        <p><?php echo ($nilai) ? htmlspecialchars($nilai['NILAI']) : "Belum ada nilai."; ?></p>
    <?php else: ?>
        <p>Tugas tidak ditemukan.</p>
    <?php endif; ?>

    <p><a href="daftar_tugas.php">Kembali ke Daftar Tugas</a></p>
</body>
</html>
