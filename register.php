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
$success_message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $namalengkap = $_POST['namalengkap'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $nik = $_POST['nik'];
    $peran = $_POST['peran'];
    $kelas = $_POST['kelas'] ?? null;
    $mapel = $_POST['mapel'] ?? null;
    $anak = $_POST['anak'] ?? null;

    // Validasi input
    if (empty($namalengkap) || empty($password) || empty($confirm_password) || empty($nik) || empty($peran)) {
        $errors[] = "Semua kolom wajib diisi.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Konfirmasi sandi tidak cocok.";
    } else {
        // Cek apakah ID atau NIK sudah ada
        $stmt = $conn->prepare("SELECT ID FROM data_user WHERE NAMALENGKAP = ? OR NIK = ?");
        $stmt->bind_param("ss", $namalengkap, $nik);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errors[] = "Nama Lengkap atau NIK sudah digunakan.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user ke database
            $stmt = $conn->prepare("INSERT INTO data_user (NAMALENGKAP, PASSWORD, NIK, PERAN, KELAS, MAPEL, ANAKSISWA) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $namalengkap, $hashed_password, $nik, $peran, $kelas, $mapel, $anak);
            
            if ($stmt->execute()) {
                // Ambil ID baru
                $user_id = $stmt->insert_id;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['peran'] = $peran;
                
                // Arahkan ke halaman sesuai peran
                switch ($peran) {
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
                $errors[] = "Terjadi kesalahan saat registrasi.";
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-b from-[#1E0342] to-[#433D8B] flex items-center justify-center min-h-screen px-4 py-16">

    <div class="w-full max-w-md p-8 space-y-8 bg-white rounded-xl shadow-md transform transition duration-500 hover:scale-105">
        <div class="flex justify-center mb-4">
            <div class="bg-[#1E0342] p-4 rounded-full">
                <img src="logobadag.png" alt="Logo" class="w-40 h-auto">
            </div>
        </div>

        <h2 class="text-center text-2xl font-semibold text-gray-800">Registrasi Pengguna</h2>

        <?php if (!empty($errors)): ?>
            <div class="text-red-500 text-center">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" class="space-y-4">
            <input type="text" id="namalengkap" name="namalengkap" placeholder="Nama Lengkap" value="<?php echo isset($_POST['namalengkap']) ? htmlspecialchars($_POST['namalengkap']) : ''; ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <input type="password" id="password" name="password" placeholder="Sandi" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Konfirmasi Sandi" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <input type="text" id="nik" name="nik" placeholder="NIK" value="<?php echo isset($_POST['nik']) ? htmlspecialchars($_POST['nik']) : ''; ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <select id="peran" name="peran" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Pilih Peran</option>
                <option value="guru">Guru</option>
                <option value="siswa">Siswa</option>
                <option value="orangtua">Orangtua</option>
            </select>

            <div id="popup-content" class="space-y-4"></div>

            <input type="submit" value="Registrasi" class="w-full bg-indigo-600 text-white font-semibold py-2 rounded-lg hover:bg-indigo-700 transition duration-300">
        </form>

        <p class="text-center text-gray-600">Sudah memiliki akun? <a href="login.php" class="text-indigo-600 hover:underline">Login di sini</a></p>
    </div>

    <script>
        document.getElementById('peran').addEventListener('change', function() {
            var peran = this.value;
            var popupContent = document.getElementById('popup-content');
            popupContent.innerHTML = ''; // Clear previous content

            if (peran === 'guru') {
                popupContent.innerHTML = `
                    <label for="mapel" class="block text-sm font-medium text-gray-700">Mata Pelajaran:</label>
                    <select id="mapel" name="mapel" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Pilih Mata Pelajaran</option>
                        <option value="Produktif">Produktif</option>
                        <option value="PPKN">PPKN</option>
                        <option value="PAI">PAI</option>
                        <option value="Matematika">Matematika</option>
                        <option value="Bahasa Inggris">Bahasa Inggris</option>
                        <option value="PKK">PKK</option>
                        <option value="MPKK">MPKK</option>
                        <option value="Bahasa Indonesia">Bahasa Indonesia</option>
                    </select>
                `;
            } else if (peran === 'siswa') {
                popupContent.innerHTML = `
                    <label for="kelas" class="block text-sm font-medium text-gray-700">Kelas:</label>
                    <select id="kelas" name="kelas" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Pilih Kelas</option>
                        <option value="XII RPL 1">XII RPL 1</option>
                        <option value="XII RPL 2">XII RPL 2</option>
                    </select>
                `;
            } else if (peran === 'orangtua') {
                popupContent.innerHTML = `
                    <label for="kelas" class="block text-sm font-medium text-gray-700">Kelas Anak:</label>
                    <select id="kelas" name="kelas" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" onchange="loadNamaAnak()">
                        <option value="">Pilih Kelas</option>
                        <option value="XII RPL 1">XII RPL 1</option>
                        <option value="XII RPL 2">XII RPL 2</option>
                    </select>

                    <label for="anak" class="block text-sm font-medium text-gray-700">Nama Anak:</label>
                    <select id="anak" name="anak" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Pilih Nama Anak</option>
                    </select>
                `;
            }
        });

        function loadNamaAnak() {
            var kelas = document.getElementById('kelas').value;
            var anakSelect = document.getElementById('anak');
            anakSelect.innerHTML = '<option value="">Loading...</option>';

            if (kelas) {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'get_anak.php?kelas=' + kelas, true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var anakList = JSON.parse(xhr.responseText);
                        anakSelect.innerHTML = '<option value="">Pilih Nama Anak</option>';
                        anakList.forEach(function(anak) {
                            var option = document.createElement('option');
                            option.value = anak.NAMALENGKAP;
                            option.textContent = anak.NAMALENGKAP;
                            anakSelect.appendChild(option);
                        });
                    }
                };
                xhr.send();
            }
        }
    </script>

</body>
</html>
