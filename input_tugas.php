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

// Ambil mata pelajaran user yang login
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT MAPEL FROM data_user WHERE ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($mapel);
$stmt->fetch();
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul_tugas = $_POST['judul_tugas'];
    $tenggat_waktu = $_POST['tenggat_waktu'];
    $deskripsi = $_POST['deskripsi'];

    // Validasi input
    if (empty($judul_tugas) || empty($tenggat_waktu) || empty($deskripsi)) {
        $errors[] = "Semua kolom wajib diisi.";
    } else {
        // Insert tugas ke database
        $stmt = $conn->prepare("INSERT INTO tugas (JUDUL_TUGAS, TANGGAL_TUGAS, DESKRIPSI, MAPEL) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $judul_tugas, $tenggat_waktu, $deskripsi, $mapel);
        
        if ($stmt->execute()) {
            $success_message = "Tugas berhasil ditambahkan.";
        } else {
            $errors[] = "Terjadi kesalahan saat menambahkan tugas.";
        }
        $stmt->close();

        // Redirect to riwayat_tugas.php
        echo "<script>
                setTimeout(function() {
                    window.location.href = 'riwayat_tugas.php';
                }, 1000); // 1 second delay for user to see the success message
              </script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Tugas</title>
    <link rel="stylesheet" href="input_tugas.css">
</head>
<body>
<div class="logo-top-left">
    <img src="logobadag.png" alt="Logo">
</div>

<div class="page-title">Input Tugas</div>

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

        <form action="input_tugas.php" method="POST">
            <div class="form-group">
                <label for="judul_tugas">Judul Tugas:</label>
                <input type="text" id="judul_tugas" name="judul_tugas" value="<?php echo isset($_POST['judul_tugas']) ? htmlspecialchars($_POST['judul_tugas']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="tenggat_waktu">Tanggal Tugas:</label>
                <input type="datetime-local" id="tenggat_waktu" name="tenggat_waktu" value="<?php echo isset($_POST['tenggat_waktu']) ? htmlspecialchars($_POST['tenggat_waktu']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="deskripsi">Deskripsi Tugas:</label>
                <textarea id="deskripsi" name="deskripsi" required><?php echo isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="mapel">Mata Pelajaran:</label>
                <input type="text" id="mapel" name="mapel" value="<?php echo htmlspecialchars($mapel); ?>" readonly>
            </div>

            <div class="form-group">
                <button type="submit">Kirim</button>
            </div>
        </form>
    </div>
</div>
<a class="backguru" href="guru.php">Kembali</a>
</body>
</html>
