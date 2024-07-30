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

// Ambil nama siswa
$nama_siswa = null;

// Ambil nama siswa dari sesi
$stmt = $conn->prepare("SELECT NAMALENGKAP FROM data_user WHERE ID = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($nama_siswa);
$stmt->fetch();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda Siswa</title>
    <link rel="stylesheet" href="siswa.css">
</head>
<body>
    <div class="notification">
        Selamat Datang, <?php echo htmlspecialchars($nama_siswa); ?>
    </div>

    <div class="logo-top-left">
        <img src="logobadag.png" alt="Logo">
    </div>

    <div class="page-title">Daftar Mapel</div>

    <div class="subjects-container">
        <a href="daftar_tugas.php?mapel=bahasa inggris">Bahasa Inggris</a>
        <a href="daftar_tugas.php?mapel=produktif">Produktif</a>
        <a href="daftar_tugas.php?mapel=matematika">Matematika</a>
        <a href="daftar_tugas.php?mapel=pkk">PKK</a>
        <a href="daftar_tugas.php?mapel=pai">PAI</a>
        <a href="daftar_tugas.php?mapel=ppkn">PPKN</a>
        <a href="daftar_tugas.php?mapel=bahasa indonesia">Bahasa Indonesia</a>
        <a href="daftar_tugas.php?mapel=mpkk">MPKK</a>
        <a href="daftar_tugas.php?mapel=sejarah">Sejarah</a>
    </div>

    <a class="logout" href="logout.php">Logout</a>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                const notification = document.querySelector('.notification');
                if (notification) {
                    notification.style.display = 'none';
                }
            }, 3000); // 3 seconds
        });
    </script>
</body>
</html>
