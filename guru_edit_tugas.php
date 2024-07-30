<?php
session_start();

if ($_SESSION['peran'] !== 'guru') {
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

$errors = [];
$success_message = '';

// Ambil data tugas berdasarkan ID tugas
if (isset($_GET['id_tugas'])) {
    $id_tugas = intval($_GET['id_tugas']);
    
    $stmt = $conn->prepare("SELECT JUDUL_TUGAS, TANGGAL_TUGAS, DESKRIPSI, MAPEL FROM tugas WHERE ID_TUGAS = ?");
    $stmt->bind_param("i", $id_tugas);
    $stmt->execute();
    $stmt->bind_result($judul_tugas, $tenggat_waktu, $deskripsi, $mapel);
    $stmt->fetch();
    $stmt->close();
}

// Handle task update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_tugas'])) {
    $id_tugas = intval($_POST['id_tugas']);
    $judul_tugas = $_POST['judul_tugas'];
    $tenggat_waktu = $_POST['tenggat_waktu'];
    $deskripsi = $_POST['deskripsi'];
    $mapel = $_POST['mapel'];

    // Validasi input
    if (empty($judul_tugas) || empty($tenggat_waktu) || empty($deskripsi) || empty($mapel)) {
        $errors[] = "Semua kolom wajib diisi.";
    } else {
        // Update tugas
        $stmt = $conn->prepare("UPDATE tugas SET JUDUL_TUGAS = ?, TANGGAL_TUGAS = ?, DESKRIPSI = ?, MAPEL = ? WHERE ID_TUGAS = ?");
        $stmt->bind_param("ssssi", $judul_tugas, $tenggat_waktu, $deskripsi, $mapel, $id_tugas);

        if ($stmt->execute()) {
            $success_message = "Tugas berhasil diperbarui.";
        } else {
            $errors[] = "Terjadi kesalahan saat memperbarui tugas.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tugas</title>
    <link rel="stylesheet" href="guru_edit_tugas.css">
</head>
<body>
<div class="logo-top-left">
    <img src="logobadag.png" alt="Logo">
</div>

<div class="page-title">Edit Tugas</div>

<?php if (!empty($success_message)): ?>
    <div class="success-message">
        <p><?php echo htmlspecialchars($success_message); ?></p>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="error-messages">
        <?php foreach ($errors as $error): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="container">
    <div class="form-box">
        <form action="guru_edit_tugas.php" method="POST">
            <input type="hidden" name="id_tugas" value="<?php echo htmlspecialchars($id_tugas); ?>">

            <div class="form-group">
                <label for="judul_tugas">Judul Tugas:</label>
                <input type="text" id="judul_tugas" name="judul_tugas" value="<?php echo htmlspecialchars($judul_tugas); ?>">
            </div>
            
            <div class="form-group">
                <label for="tenggat_waktu">Tenggat Waktu:</label>
                <input type="datetime-local" id="tenggat_waktu" name="tenggat_waktu" value="<?php echo htmlspecialchars($tenggat_waktu); ?>">
            </div>
            
            <div class="form-group">
                <label for="deskripsi">Deskripsi:</label>
                <textarea id="deskripsi" name="deskripsi"><?php echo htmlspecialchars($deskripsi); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="mapel">Mata Pelajaran:</label>
                <input type="text" id="mapel" name="mapel" value="<?php echo htmlspecialchars($mapel); ?>">
            </div>
            
            <button type="submit" name="update_tugas">Perbarui Tugas</button>
        </form>
    </div>
</div>

<a class="backguru" href="riwayat_tugas.php">Kembali</a>
</body>
</html>
