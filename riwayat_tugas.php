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

// Handle task deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_tugas'])) {
    $id_tugas = $_POST['id_tugas'];

    // Hapus entri terkait di tabel nilai_siswa terlebih dahulu
    $stmt = $conn->prepare("DELETE FROM nilai_siswa WHERE ID_TUGAS = ?");
    $stmt->bind_param("i", $id_tugas);
    $stmt->execute();
    $stmt->close();

    // Hapus tugas
    $stmt = $conn->prepare("DELETE FROM tugas WHERE ID_TUGAS = ?");
    $stmt->bind_param("i", $id_tugas);
    
    if ($stmt->execute()) {
        $success_message = "Tugas berhasil dihapus.";
    } else {
        $errors[] = "Terjadi kesalahan saat menghapus tugas.";
    }
    $stmt->close();
}

// Retrieve tugas based on the teacher's mapel
$tugas_list = [];
$stmt = $conn->prepare("SELECT ID_TUGAS, JUDUL_TUGAS, TANGGAL_TUGAS, DESKRIPSI FROM tugas WHERE MAPEL = ?");
$stmt->bind_param("s", $mapel);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $tugas_list[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Tugas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
        .animate-fade-in {
            animation: fadeIn 1s ease-in-out;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-b from-[#1E0342] to-[#433D8B] min-h-screen flex flex-col items-center justify-center p-4">

    <!-- Logo -->
    <div class="absolute top-4 left-4 sm:top-6 sm:left-6">
        <img src="logobadag.png" alt="Logo" class="w-20 sm:w-24 md:w-32">
    </div>

    <!-- Page Title -->
    <h1 class="text-2xl sm:text-3xl text-white font-semibold mb-6 text-center mt-16">Riwayat Tugas</h1>

    <!-- Main Content Container -->
    <div class="tasks-container  p-5 sm:p-10 rounded-lg  w-full sm:max-w-4xl mt-10 animate-fade-in">
        <!-- Success and Error Messages -->
        <div class="mb-4">
            <?php if (!empty($success_message)): ?>
                <div class="p-4 bg-green-500 text-white rounded-lg shadow-lg transition-transform transform scale-100 hover:scale-105 text-center">
                    <p><?php echo htmlspecialchars($success_message); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="p-4 bg-red-500 text-white rounded-lg shadow-lg transition-transform transform scale-100 hover:scale-105 text-center">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tasks Container -->
        <div class="bg-white p-6 rounded-lg shadow-lg overflow-x-auto">
            <table class="w-full border-collapse text-sm sm:text-base">
                <thead class="bg-[#433D8B] text-white">
                    <tr>
                        <th class="p-3">Judul Tugas</th>
                        <th class="p-3">Tanggal Tugas</th>
                        <th class="p-3">Deskripsi</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tugas_list)): ?>
                        <tr>
                            <td colspan="4" class="p-3 text-center text-gray-500">Tidak ada tugas yang tersedia untuk mata pelajaran ini.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tugas_list as $tugas): ?>
                            <tr class="hover:bg-gray-100 transition-colors duration-300">
                                <td class="p-3"><?php echo htmlspecialchars($tugas['JUDUL_TUGAS']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($tugas['TANGGAL_TUGAS']); ?></td>
                                <td class="p-3"><?php echo htmlspecialchars($tugas['DESKRIPSI']); ?></td>
                                <td class="p-3 flex space-x-2">
                                    <form action="riwayat_tugas.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="id_tugas" value="<?php echo $tugas['ID_TUGAS']; ?>">
                                        <input type="submit" name="delete_tugas" value="Hapus" class="bg-red-500 text-white py-1 px-3 rounded-lg hover:bg-red-700 transition duration-300 cursor-pointer" onclick="return confirm('Apakah Anda yakin ingin menghapus tugas ini?')">
                                    </form>
                                    <a href="detail_tugas.php?id_tugas=<?php echo $tugas['ID_TUGAS']; ?>" class="bg-blue-500 text-white py-1 px-3 rounded-lg hover:bg-blue-700 transition duration-300">Nilai</a>
                                    <a href="guru_edit_tugas.php?id_tugas=<?php echo $tugas['ID_TUGAS']; ?>" class="bg-yellow-500 text-white py-1 px-3 rounded-lg hover:bg-yellow-700 transition duration-300">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Back Button -->
    <a href="guru.php" class="fixed bottom-5 right-5 bg-white text-[#433D8B] font-semibold px-4 py-2 rounded-xl shadow-lg hover:bg-[#433D8B] hover:text-white transition duration-300">Kembali</a>
</body>
</html>



