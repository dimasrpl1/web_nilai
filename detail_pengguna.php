<?php
session_start();

// Periksa apakah pengguna sudah login dan perannya adalah admin
if (!isset($_SESSION['peran']) || $_SESSION['peran'] !== 'admin') {
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

// Ambil data pengguna berdasarkan ID
$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT NAMALENGKAP, PASSWORD, NIK, PERAN, MAPEL, ANAKSISWA, KELAS FROM data_user WHERE ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengguna</title>
    <link rel="stylesheet" href="detail_pengguna.css">
</head>
<body>
<div class="logo-top-left">
    <img src="logobadag.png" alt="Logo">
</div>

<div class="page-title">Detail Pengguna</div>

<div class="container">
    <div class="form-box">
        <div class="form-group">
            <label for="namalengkap">Nama Lengkap:</label>
            <div class="kotak">
                <p><?php echo htmlspecialchars($user['NAMALENGKAP'] ?? ''); ?></p>
            </div>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <div class="kotak">
                <p><?php echo htmlspecialchars($user['PASSWORD'] ?? ''); ?></p>
            </div>
        </div>
        <div class="form-group">
            <label for="nik">NIK:</label>
            <div class="kotak">
                <p><?php echo htmlspecialchars($user['NIK'] ?? ''); ?></p>
            </div>
        </div>
        <div class="form-group">
            <label for="peran">Peran:</label>
            <div class="kotak">
                <p><?php echo htmlspecialchars($user['PERAN'] ?? ''); ?></p>
            </div>
        </div>
        <?php if ($user['PERAN'] == 'guru'): ?>
            <div class="form-group">
                <label for="mapel">Mata Pelajaran:</label>
                <div class="kotak">
                    <p><?php echo htmlspecialchars($user['MAPEL'] ?? ''); ?></p>
                </div>
            </div>
        <?php elseif ($user['PERAN'] == 'orangtua'): ?>
            <div class="form-group">
                <label for="anak">Nama Anak:</label>
                <div class="kotak">
                    <p><?php echo htmlspecialchars($user['ANAKSISWA'] ?? ''); ?></p>
                </div>
            </div>
        <?php elseif ($user['PERAN'] == 'siswa'): ?>
            <div class="form-group">
                <label for="kelas">Kelas:</label>
                <div class="kotak">
                    <p><?php echo htmlspecialchars($user['KELAS'] ?? ''); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<a class="backguru" href="admin.php">Kembali</a>
</body>
</html>
