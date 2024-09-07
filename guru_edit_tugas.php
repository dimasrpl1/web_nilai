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
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-b from-[#1E0342] to-[#433D8B] min-h-screen flex flex-col items-center p-4">
    <!-- Logo -->
    <div class="absolute top-4 left-4 sm:top-6 sm:left-6">
        <img src="logobadag.png" alt="Logo" class="w-20 sm:w-24 md:w-32">
    </div>

    <!-- Page Title -->
    <h1 class="text-2xl sm:text-3xl text-white font-semibold mb-6 text-center">Edit Tugas</h1>

    <!-- Success and Error Messages -->
    <?php if (!empty($success_message)): ?>
        <div class="mb-4 p-4 bg-green-500 text-white rounded-lg shadow-lg transition-transform transform scale-100 hover:scale-105">
            <p><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="mb-4 p-4 bg-red-500 text-white rounded-lg shadow-lg transition-transform transform scale-100 hover:scale-105">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Form Container -->
    <div class="bg-white p-6 sm:p-8 rounded-lg shadow-lg w-full max-w-md sm:max-w-lg">
        <form action="guru_edit_tugas.php" method="POST" class="space-y-6">
            <input type="hidden" name="id_tugas" value="<?php echo htmlspecialchars($id_tugas); ?>">

            <div class="form-group">
                <label for="judul_tugas" class="block text-base sm:text-lg font-medium text-gray-700">Judul Tugas:</label>
                <input type="text" id="judul_tugas" name="judul_tugas" value="<?php echo htmlspecialchars($judul_tugas); ?>" required class="mt-1 p-2 border border-gray-300 rounded-lg w-full">
            </div>
            
            <div class="form-group">
                <label for="tenggat_waktu" class="block text-base sm:text-lg font-medium text-gray-700">Tenggat Waktu:</label>
                <input type="datetime-local" id="tenggat_waktu" name="tenggat_waktu" value="<?php echo htmlspecialchars($tenggat_waktu); ?>" required class="mt-1 p-2 border border-gray-300 rounded-lg w-full">
            </div>
            
            <div class="form-group">
                <label for="deskripsi" class="block text-base sm:text-lg font-medium text-gray-700">Deskripsi:</label>
                <textarea id="deskripsi" name="deskripsi" required class="mt-1 p-2 border border-gray-300 rounded-lg w-full"><?php echo htmlspecialchars($deskripsi); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="mapel" class="block text-base sm:text-lg font-medium text-gray-700">Mata Pelajaran:</label>
                <input type="text" id="mapel" name="mapel" value="<?php echo htmlspecialchars($mapel); ?>" required class="mt-1 p-2 border border-gray-300 rounded-lg w-full">
            </div>
            
            <div class="form-group">
                <button type="submit" name="update_tugas" class="w-full bg-[#433D8B] text-white py-2 px-4 rounded-lg hover:bg-[#1E0342] transition duration-300">Perbarui Tugas</button>
            </div>
        </form>
    </div>

    <!-- Back Button -->
    <a href="riwayat_tugas.php" class="fixed bottom-5 right-5 bg-white text-[#433D8B] font-semibold px-4 py-2 rounded-xl shadow-lg hover:bg-[#433D8B] hover:text-white transition duration-300">Kembali</a>
</body>
</html>
