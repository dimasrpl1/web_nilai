<?php
session_start();

// Periksa apakah pengguna sudah login dan perannya adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['peran'] !== 'admin') {
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

// Ambil nama admin dari sesi
$stmt = $conn->prepare("SELECT NAMALENGKAP FROM data_user WHERE ID = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($nama_admin);
$stmt->fetch();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda Admin</title>
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
        Selamat Datang, <?php echo htmlspecialchars($nama_admin); ?>
    </div>

    <!-- Navigation Links -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="input_pengguna.php" class="block p-6 bg-[#433D8B] text-white rounded-lg shadow-lg hover:bg-[#1E0342] transition duration-300">
            <div class="text-xl font-semibold mb-2">Input Pengguna</div>
            <p>Tambahkan pengguna baru ke dalam sistem.</p>
        </a>
        <a href="data_pengguna.php" class="block p-6 bg-[#433D8B] text-white rounded-lg shadow-lg hover:bg-[#1E0342] transition duration-300">
            <div class="text-xl font-semibold mb-2">Data Pengguna</div>
            <p>Lihat dan kelola data pengguna yang sudah terdaftar.</p>
        </a>
    </div>

    <!-- Logout Button -->
    <a href="logout.php" class="fixed bottom-5 right-5 bg-white text-[#433D8B] font-semibold px-4 py-2 sm:px-5 sm:py-3 rounded-xl shadow-lg hover:bg-[#433D8B] hover:text-white transition duration-300">
        Keluar
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
