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
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-b from-[#1E0342] to-[#433D8B] min-h-screen flex flex-col items-center justify-center p-6">
    <!-- Logo -->
    <div class="absolute top-5 left-5">
        <img src="logobadag.png" alt="Logo" class="w-24 sm:w-32">
    </div>

    <!-- Welcome Notification -->
    <div id="notification" class="fixed top-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-4 py-3 rounded-xl shadow-lg transition-opacity duration-500 ease-in-out opacity-100 max-w-xs text-center">
        Selamat Datang, <?php echo htmlspecialchars($nama_lengkap); ?>
    </div>

    <!-- Navigation Links -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <a href="input_tugas.php" class="block p-6 bg-[#433D8B] text-white rounded-lg shadow-lg hover:bg-[#1E0342] transition duration-300">
                <div class="text-xl font-semibold mb-2">Input Tugas</div>
                <p>Tambahkan dan kelola tugas yang akan diberikan kepada siswa.</p>
            </a>
            <a href="riwayat_tugas.php" class="block p-6 bg-[#433D8B] text-white rounded-lg shadow-lg hover:bg-[#1E0342] transition duration-300">
                <div class="text-xl font-semibold mb-2">Riwayat Tugas</div>
                <p>Review tugas yang telah diberikan dan nilai siswa.</p>
            </a>
        </div>
    </div>

    <!-- Logout Button -->
    <a href="logout.php" class="fixed bottom-5 right-5 bg-white text-[#433D8B] font-semibold px-4 py-2 sm:px-5 sm:py-3 rounded-xl shadow-lg hover:bg-[#433D8B] hover:text-white transition duration-300">
        Logout
    </a>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                const notification = document.getElementById('notification');
                if (notification) {
                    notification.classList.add('opacity-0');
                    setTimeout(() => notification.style.display = 'none', 500); 
                }
            }, 3000); // Hilangkan notifikasi setelah 3 detik
        });
    </script>
</body>
</html>
