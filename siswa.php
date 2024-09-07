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
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-b from-[#1E0342] to-[#433D8B] min-h-screen flex flex-col items-center justify-center p-4">
    
    <!-- Notification -->
    <div id="notification" class="fixed top-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-4 py-3 rounded-xl shadow-lg transition-opacity duration-500 ease-in-out opacity-100 max-w-xs text-center">
        Selamat Datang, <?php echo htmlspecialchars($nama_siswa); ?>
    </div>

    <!-- Logo -->
    <div class="absolute top-5 left-5">
        <img src="logobadag.png" alt="Logo" class="w-24 sm:w-32">
    </div>

    <!-- Page Title -->
    <h1 class="text-white text-2xl sm:text-4xl font-semibold mt-10 animate-fade-in-down text-center">
        Daftar Mata Pelajaran
    </h1>

    <!-- Subjects Container -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 bg-white p-5 sm:p-10 rounded-lg shadow-xl mt-10 transform transition duration-500 hover:scale-105 max-w-full sm:max-w-4xl">
        <?php 
        $subjects = ['Bahasa Inggris', 'Produktif', 'Matematika', 'PKK', 'PAI', 'PPKN', 'Bahasa Indonesia', 'MPKK', 'Sejarah'];
        foreach ($subjects as $subject) {
            echo "<a href='daftar_tugas.php?mapel=".strtolower(str_replace(' ', ' ', $subject))."' class='bg-white text-[#1E0342] text-center font-semibold py-3 px-4 sm:py-4 sm:px-6 rounded-lg border-2 border-[#433D8B] hover:bg-[#433D8B] hover:text-white transition-all duration-300'>
                {$subject}
            </a>";
        }
        ?>
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
