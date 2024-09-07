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

// Retrieve task details
$id_tugas = intval($_GET['id_tugas']);
$stmt = $conn->prepare("SELECT JUDUL_TUGAS, DESKRIPSI FROM tugas WHERE ID_TUGAS = ?");
$stmt->bind_param("i", $id_tugas);
$stmt->execute();
$stmt->bind_result($judul_tugas, $deskripsi);
$stmt->fetch();
$stmt->close();

// Handle nilai submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_nilai'])) {
    $id_siswa = intval($_POST['id_siswa']);
    $nilai = intval($_POST['nilai']);

    // Validasi nilai
    if ($nilai < 0 || $nilai > 100) {
        $errors[] = "Nilai harus antara 0 dan 100.";
    } else {
        // Check if nilai already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM nilai_siswa WHERE ID_TUGAS = ? AND SISWA_ID = ?");
        $stmt->bind_param("ii", $id_tugas, $id_siswa);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            // Update nilai
            $stmt = $conn->prepare("UPDATE nilai_siswa SET NILAI = ? WHERE ID_TUGAS = ? AND SISWA_ID = ?");
            $stmt->bind_param("iii", $nilai, $id_tugas, $id_siswa);
        } else {
            // Insert nilai
            $stmt = $conn->prepare("INSERT INTO nilai_siswa (ID_TUGAS, SISWA_ID, NILAI) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $id_tugas, $id_siswa, $nilai);
        }

        if ($stmt->execute()) {
            $success_message = "Nilai berhasil disimpan.";
        } else {
            $errors[] = "Terjadi kesalahan saat menyimpan nilai.";
        }
        $stmt->close();
    }
}

// Retrieve students and their grades with pagination
$limit = 5;  // Changed from 7 to 5
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$siswa_list = [];
$stmt = $conn->prepare("SELECT u.ID AS ID_SISWA, u.NAMALENGKAP, n.NILAI 
                        FROM data_user u 
                        LEFT JOIN nilai_siswa n ON u.ID = n.SISWA_ID AND n.ID_TUGAS = ? 
                        WHERE u.PERAN = 'siswa'
                        LIMIT ? OFFSET ?");
$stmt->bind_param("iii", $id_tugas, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $siswa_list[] = $row;
}

$stmt->close();

// Get total number of students
$total_result = $conn->query("SELECT COUNT(*) AS total FROM data_user WHERE PERAN = 'siswa'");
$total_row = $total_result->fetch_assoc();
$total_students = $total_row['total'];
$total_pages = ceil($total_students / $limit);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tugas</title>
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
<body class="bg-gradient-to-b from-[#1E0342] to-[#433D8B] min-h-screen flex flex-col items-center p-4">

    <!-- Logo -->
    <div class="absolute top-4 left-4 sm:top-6 sm:left-6">
        <img src="logobadag.png" alt="Logo" class="w-20 sm:w-24 md:w-32">
    </div>

    <!-- Page Title -->
    <h1 class="text-3xl font-semibold text-white mb-8 mt-16 text-center">Detail Tugas</h1>

    <!-- Detail Tugas Container -->
    <div class="w-full max-w-4xl bg-white p-6 rounded-lg shadow-lg animate-fade-in">

        <!-- Success and Error Messages -->
        <div class="mb-4">
            <?php if (!empty($success_message)): ?>
                <div class="p-4 bg-green-500 text-white rounded-lg shadow-lg text-center">
                    <p><?php echo htmlspecialchars($success_message); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="p-4 bg-red-500 text-white rounded-lg shadow-lg text-center">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Task Details -->
        <div class="mb-6">
            <h2 class="text-2xl font-semibold mb-2"><?php echo htmlspecialchars($judul_tugas); ?></h2>
            <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($deskripsi)); ?></p>
        </div>

        <!-- Student Grades Table -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse bg-white rounded-lg shadow-lg">
                <thead class="bg-[#433D8B] text-white">
                    <tr>
                        <th class="p-3">Nama Siswa</th>
                        <th class="p-3">Nilai</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($siswa_list)): ?>
                        <tr>
                            <td colspan="3" class="p-3 text-center text-gray-500">Tidak ada data nilai siswa.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($siswa_list as $siswa): ?>
                            <tr class="border-b hover:bg-gray-100 transition-colors duration-300">
                                <td class="p-3"><?php echo htmlspecialchars($siswa['NAMALENGKAP']); ?></td>
                                <td class="p-3">
                                    <form action="detail_tugas.php?id_tugas=<?php echo $id_tugas; ?>" method="POST" class="flex items-center gap-2">
                                        
                                        <input type="hidden" name="id_siswa" value="<?php echo $siswa['ID_SISWA']; ?>">
                                        <input type="number" name="nilai" value="<?php echo htmlspecialchars($siswa['NILAI'] ?? ''); ?>" class="border rounded-md p-2 w-24" required min="0" max="100">
                                        <td class="p-3">
                                        <input type="submit" name="submit_nilai" value="Simpan Nilai" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-300 cursor-pointer">
                                        </td>
                                    </form>
                                </td>
                                
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex justify-center">
            <?php if ($total_pages > 1): ?>
                <ul class="flex space-x-2">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li>
                            <a href="detail_tugas.php?id_tugas=<?php echo $id_tugas; ?>&page=<?php echo $i; ?>" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-300"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- Back Button -->
    <a href="riwayat_tugas.php" class="fixed bottom-5 right-5 bg-white text-[#433D8B] font-semibold px-4 py-2 rounded-xl shadow-lg hover:bg-[#433D8B] hover:text-white transition duration-300">Kembali</a>
</body>
</html>
