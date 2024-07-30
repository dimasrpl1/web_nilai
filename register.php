<?php
session_start();

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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $namalengkap = $_POST['namalengkap'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nik = $_POST['nik'];
    $peran = $_POST['peran'];
    $kelas = $_POST['kelas'] ?? null;
    $mapel = $_POST['mapel'] ?? null;
    $anak = $_POST['anak'] ?? null;

    // Validasi input
    if (empty($namalengkap) || empty($password) || empty($confirm_password) || empty($nik) || empty($peran)) {
        $errors[] = "Semua kolom wajib diisi.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Konfirmasi sandi tidak cocok.";
    } else {
        // Cek apakah ID atau NIK sudah ada
        $stmt = $conn->prepare("SELECT ID FROM data_user WHERE NAMALENGKAP = ? OR NIK = ?");
        $stmt->bind_param("ss", $namalengkap, $nik);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errors[] = "Nama Lengkap atau NIK sudah digunakan.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user ke database
            $stmt = $conn->prepare("INSERT INTO data_user (NAMALENGKAP, PASSWORD, NIK, PERAN, KELAS, MAPEL, ANAKSISWA) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $namalengkap, $hashed_password, $nik, $peran, $kelas, $mapel, $anak);
            
            if ($stmt->execute()) {
                // Ambil ID baru
                $user_id = $stmt->insert_id;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['peran'] = $peran;
                
                // Arahkan ke halaman sesuai peran
                switch ($peran) {
                    case 'guru':
                        header("Location: guru.php");
                        break;
                    case 'siswa':
                        header("Location: siswa.php");
                        break;
                    case 'orangtua':
                        header("Location: orangtua.php");
                        break;
                }
                exit();
            } else {
                $errors[] = "Terjadi kesalahan saat registrasi.";
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <div class="wrapper">
        <div class="logo-container">
            <img src="logonescore.png" alt="Logo">
        </div>
        <div class="register-container">
            <h2>Registrasi</h2>

            <?php if (!empty($errors)): ?>
                <div style="color: red;">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <input type="text" id="namalengkap" name="namalengkap" placeholder="Nama Lengkap" value="<?php echo isset($_POST['namalengkap']) ? htmlspecialchars($_POST['namalengkap']) : ''; ?>" required>
                <input type="password" id="password" name="password" placeholder="Sandi" required>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Konfirmasi Sandi" required>
                <input type="text" id="nik" name="nik" placeholder="NIK" value="<?php echo isset($_POST['nik']) ? htmlspecialchars($_POST['nik']) : ''; ?>" required>
                <select id="peran" name="peran" required>
                    <option value="">Pilih Peran</option>
                    <option value="guru">Guru</option>
                    <option value="siswa">Siswa</option>
                    <option value="orangtua">Orangtua</option>
                </select>

                <!-- Elemen tambahan akan ditambahkan di sini -->
                <div id="popup-content"></div>

                <input type="submit" value="Registrasi">
            </form>

            <a href="login.php">Kembali ke Login</a>
        </div>
    </div>

    <script src="register.js"></script>
</body>
</html>
