<?php
session_start();

// Periksa apakah pengguna sudah login dan perannya adalah admin
if (!isset($_SESSION['peran']) || $_SESSION['peran'] !== 'admin') {
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

// Ambil data pengguna berdasarkan ID
$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT NAMALENGKAP, PASSWORD, NIK, PERAN, MAPEL, ANAKSISWA, KELAS FROM data_user WHERE ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $namalengkap = $_POST['namalengkap'];
    $password = $_POST['password'];
    $nik = $_POST['nik'];
    $peran = $_POST['peran'];
    $mapel = $_POST['mapel'] ?? null;
    $anak = $_POST['anak'] ?? null;
    $kelas = $_POST['kelas'] ?? null;

    // Validasi input
    if (empty($namalengkap) || empty($password) || empty($nik) || empty($peran)) {
        $errors[] = "Semua kolom wajib diisi.";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update pengguna
        $stmt = $conn->prepare("UPDATE data_user SET NAMALENGKAP = ?, PASSWORD = ?, NIK = ?, PERAN = ?, MAPEL = ?, ANAKSISWA = ?, KELAS = ? WHERE ID = ?");
        $stmt->bind_param("sssssssi", $namalengkap, $hashed_password, $nik, $peran, $mapel, $anak, $kelas, $id);

        if ($stmt->execute()) {
            $success_message = "Pengguna berhasil diperbarui.";
            header("Location: data_pengguna.php"); // Redirect ke data_pengguna.php setelah berhasil
            exit();
        } else {
            $errors[] = "Terjadi kesalahan saat memperbarui pengguna.";
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
    <title>Edit Pengguna</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-b from-[#1E0342] to-[#433D8B] flex items-center justify-center min-h-screen px-4 py-8">
    <div class="absolute top-4 left-4">
        <img src="logobadag.png" alt="Logo" class="w-20 sm:w-24 md:w-32">
    </div>

    <div class="w-full max-w-lg p-6 sm:p-8 bg-white rounded-xl shadow-lg transform transition duration-500 hover:scale-105">
        <h2 class="text-center text-2xl font-semibold text-gray-800 mb-6">Edit Pengguna</h2>

        <?php if ($success_message): ?>
            <div class="bg-green-100 text-green-700 p-4 mb-4 rounded-lg">
                <p><?php echo htmlspecialchars($success_message); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 text-red-700 p-4 mb-4 rounded-lg">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="edit_pengguna.php?id=<?php echo $id; ?>" method="POST" class="space-y-6">
            <div class="form-group">
                <label for="namalengkap" class="block text-sm font-medium text-gray-700">Nama Lengkap:</label>
                <input type="text" id="namalengkap" name="namalengkap" value="<?php echo htmlspecialchars($user['NAMALENGKAP']); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            
            <div class="form-group">
                <label for="password" class="block text-sm font-medium text-gray-700">Password:</label>
                <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($user['PASSWORD']); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            
            <div class="form-group">
                <label for="nik" class="block text-sm font-medium text-gray-700">NIK:</label>
                <input type="text" id="nik" name="nik" value="<?php echo htmlspecialchars($user['NIK']); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            
            <div class="form-group">
                <label for="peran" class="block text-sm font-medium text-gray-700">Peran:</label>
                <select id="peran" name="peran" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="admin" <?php echo $user['PERAN'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="guru" <?php echo $user['PERAN'] == 'guru' ? 'selected' : ''; ?>>Guru</option>
                    <option value="siswa" <?php echo $user['PERAN'] == 'siswa' ? 'selected' : ''; ?>>Siswa</option>
                    <option value="orangtua" <?php echo $user['PERAN'] == 'orangtua' ? 'selected' : ''; ?>>Orangtua</option>
                </select>
            </div>

            <div id="additional-fields" class="space-y-4">
                <?php if ($user['PERAN'] == 'guru'): ?>
                    <div class="form-group">
                        <label for="mapel" class="block text-sm font-medium text-gray-700">Mata Pelajaran:</label>
                        <select id="mapel" name="mapel" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Pilih Mata Pelajaran</option>
                            <option value="Produktif" <?php echo $user['MAPEL'] == 'Produktif' ? 'selected' : ''; ?>>Produktif</option>
                            <option value="PPKN" <?php echo $user['MAPEL'] == 'PPKN' ? 'selected' : ''; ?>>PPKN</option>
                            <option value="PAI" <?php echo $user['MAPEL'] == 'PAI' ? 'selected' : ''; ?>>PAI</option>
                            <option value="Matematika" <?php echo $user['MAPEL'] == 'Matematika' ? 'selected' : ''; ?>>Matematika</option>
                            <option value="Bahasa Inggris" <?php echo $user['MAPEL'] == 'Bahasa Inggris' ? 'selected' : ''; ?>>Bahasa Inggris</option>
                            <option value="PKK" <?php echo $user['MAPEL'] == 'PKK' ? 'selected' : ''; ?>>PKK</option>
                            <option value="MPKK" <?php echo $user['MAPEL'] == 'MPKK' ? 'selected' : ''; ?>>MPKK</option>
                            <option value="Bahasa Indonesia" <?php echo $user['MAPEL'] == 'Bahasa Indonesia' ? 'selected' : ''; ?>>Bahasa Indonesia</option>
                        </select>
                    </div>
                <?php elseif ($user['PERAN'] == 'orangtua'): ?>
                    <div class="form-group">
                        <label for="anak" class="block text-sm font-medium text-gray-700">Nama Anak:</label>
                        <select id="anak" name="anak" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Pilih Nama Anak</option>
                            <?php
                            $conn = new mysqli($servername, $username, $password, $dbname);
                            $result = $conn->query("SELECT NAMALENGKAP FROM data_user WHERE PERAN = 'siswa'");
                            while ($row = $result->fetch_assoc()) {
                                echo '<option value="' . htmlspecialchars($row['NAMALENGKAP']) . '"' . ($user['ANAKSISWA'] == $row['NAMALENGKAP'] ? ' selected' : '') . '>' . htmlspecialchars($row['NAMALENGKAP']) . '</option>';
                            }
                            $conn->close();
                            ?>
                        </select>
                    </div>
                <?php elseif ($user['PERAN'] == 'siswa'): ?>
                    <div class="form-group">
                        <label for="kelas" class="block text-sm font-medium text-gray-700">Kelas:</label>
                        <select id="kelas" name="kelas" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Pilih Kelas</option>
                            <option value="XII RPL 1" <?php echo $user['KELAS'] == 'XII RPL 1' ? 'selected' : ''; ?>>XII RPL 1</option>
                            <option value="XII RPL 2" <?php echo $user['KELAS'] == 'XII RPL 2' ? 'selected' : ''; ?>>XII RPL 2</option>
                        </select>
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit" name="edit" class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition duration-300">Simpan Perubahan</button>
        </form>

        
    </div>
    <a href="admin.php" class="fixed bottom-5 right-5 bg-white text-[#433D8B] font-semibold px-4 py-2 sm:px-5 sm:py-3 rounded-xl shadow-lg hover:bg-[#433D8B] hover:text-white transition duration-300">
        Kembali
    </a>
</body>
</html>

