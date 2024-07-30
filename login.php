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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $namalengkap = $_POST['namalengkap'];
    $password = $_POST['password'];

    // Validasi input
    if (empty($namalengkap) || empty($password)) {
        $errors[] = "Nama Lengkap dan Password wajib diisi.";
    } else {
        // Cek kredensial pengguna
        $stmt = $conn->prepare("SELECT ID, PASSWORD, PERAN FROM data_user WHERE NAMALENGKAP = ?");
        $stmt->bind_param("s", $namalengkap);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_password, $peran);
            $stmt->fetch();
            
            // Verifikasi password
            if (password_verify($password, $hashed_password)) {
                // Set session dan arahkan ke halaman yang sesuai
                $_SESSION['user_id'] = $id;
                $_SESSION['peran'] = $peran;
                
                switch ($peran) {
                    case 'admin':
                        header("Location: admin.php");
                        break;
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
                $errors[] = "Password salah.";
            }
        } else {
            $errors[] = "Nama Lengkap tidak ditemukan.";
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
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="wrapper">
        <div class="logo-container">
            <img src="logonescore.png" alt="Logo">
        </div>

        <div class="register-container">
            <h2>Login Pengguna</h2>

            <?php if (!empty($errors)): ?>
                <div style="color: red;">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <input type="text" id="namalengkap" name="namalengkap" placeholder="Nama Lengkap" value="<?php echo isset($_POST['namalengkap']) ? htmlspecialchars($_POST['namalengkap']) : ''; ?>" required>
                <input type="password" id="password" name="password" placeholder="Password" required>
                <input type="submit" value="Login">
            </form>

            <p>Belum memiliki akun? <a href="register.php">Daftar di sini</a></p>
        </div>
    </div>
</body>
</html>
