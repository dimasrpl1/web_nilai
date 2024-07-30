<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['peran'] !== 'guru') {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root"; // Adjust as needed
$password = ""; // Adjust as needed
$dbname = "nilai_siswa"; // Adjust as needed

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Retrieve user's full name
$sql = "SELECT NAMALENGKAP FROM data_user WHERE ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->bind_result($nama_lengkap);
$stmt->fetch();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda Guru</title>
    <link rel="stylesheet" href="guru.css">
</head>
<body>
    <div class="wrapper">
        <div class="logo-top-left">
            <img src="logobadag.png" alt="Logo">
        </div>

        <div class="notification">Selamat Datang, <?php echo htmlspecialchars($nama_lengkap); ?></div>
        
        <div class="guru-subject-container">
            <a href="input_tugas.php">Input Tugas</a>
            <a href="riwayat_tugas.php">Riwayat Tugas</a>
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
    </div>
</body>
</html>
