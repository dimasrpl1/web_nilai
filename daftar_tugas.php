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

$user_id = $_SESSION['user_id']; // Get the logged-in student's ID

// Ambil parameter mata pelajaran
$mapel = isset($_GET['mapel']) ? $_GET['mapel'] : '';
$tugas = [];

if (empty($mapel)) {
    die("Parameter mapel tidak ada atau kosong.");
}

// Fungsi untuk mengubah tanggal ke format bahasa Indonesia
function format_date_indonesia($date) {
    $hari = array("Sun" => "Minggu", "Mon" => "Senin", "Tue" => "Selasa", "Wed" => "Rabu", "Thu" => "Kamis", "Fri" => "Jumat", "Sat" => "Sabtu");
    $bulan = array("01" => "Januari", "02" => "Februari", "03" => "Maret", "04" => "April", "05" => "Mei", "06" => "Juni", "07" => "Juli", "08" => "Agustus", "09" => "September", "10" => "Oktober", "11" => "November", "12" => "Desember");

    $timestamp = strtotime($date);
    $hari_indonesia = $hari[date('D', $timestamp)];
    $bulan_indonesia = $bulan[date('m', $timestamp)];
    $tanggal = date('d', $timestamp);
    $tahun = date('Y', $timestamp);

    return "$hari_indonesia, $tanggal $bulan_indonesia $tahun";
}

// Ambil tugas berdasarkan mata pelajaran dan siswa ID
$stmt = $conn->prepare("
    SELECT tugas.JUDUL_TUGAS, tugas.TANGGAL_TUGAS, tugas.DESKRIPSI, 
           COALESCE(nilai_siswa.NILAI, 'Belum dinilai') AS NILAI 
    FROM tugas 
    LEFT JOIN nilai_siswa 
    ON tugas.ID_TUGAS = nilai_siswa.ID_TUGAS AND nilai_siswa.SISWA_ID = ?
    WHERE tugas.MAPEL = ?
");
$stmt->bind_param("is", $user_id, $mapel);
$stmt->execute();
$stmt->bind_result($judul_tugas, $tanggal_tugas, $deskripsi, $nilai);

while ($stmt->fetch()) {
    $tugas[] = ['judul_tugas' => $judul_tugas, 'tanggal_tugas' => $tanggal_tugas, 'deskripsi' => $deskripsi, 'nilai' => $nilai];
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tugas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }

        .animate-fade-in {
            opacity: 0;
            animation: fadeIn 0.8s forwards;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-b from-[#1E0342] to-[#433D8B] min-h-screen flex flex-col items-center justify-center p-4">
    <!-- Logo -->
    <div class="absolute top-5 left-5">
        <img src="logobadag.png" alt="Logo" class="w-24 sm:w-32">
    </div>

    <!-- Page Title -->
    <h1 class="text-white text-2xl sm:text-4xl font-semibold mt-10 animate-fade-in text-center capitalize">
        <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($mapel))); ?>
    </h1>

    <!-- Tasks Container -->
    <div class="tasks-container bg-white p-5 sm:p-10 rounded-lg shadow-xl mt-10 animate-fade-in w-full sm:max-w-4xl">
        <table class="w-full border-collapse text-sm sm:text-base">
            <thead>
                <tr class="bg-[#433D8B] text-white">
                    <th class="p-3">Judul Tugas</th>
                    <th class="p-3">Tanggal</th>
                    <th class="p-3">Deskripsi</th>
                    <th class="p-3">Nilai</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tugas)): ?>
                <tr>
                    <td colspan="4" class="p-3 text-center text-gray-500">Tidak ada tugas yang tersedia untuk mata pelajaran ini.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($tugas as $item): ?>
                <tr class="hover:bg-gray-100 transition duration-300">
                    <td class="p-3 text-center"><?php echo htmlspecialchars($item['judul_tugas']); ?></td>
                    <td class="p-3 text-center"><?php echo htmlspecialchars(format_date_indonesia($item['tanggal_tugas'])); ?></td>
                    <td class="p-3 text-center"><?php echo htmlspecialchars($item['deskripsi']); ?></td>
                    <td class="p-3 text-center"><?php echo htmlspecialchars($item['nilai']); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Back Button -->
    <a href="siswa.php" class="fixed bottom-5 right-5 bg-white text-[#433D8B] font-semibold px-4 py-2 sm:px-5 sm:py-3 rounded-xl shadow-lg hover:bg-[#433D8B] hover:text-white transition duration-300">
        Kembali
    </a>

</body>
</html>
