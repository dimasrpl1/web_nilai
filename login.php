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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $namalengkap = $_POST['namalengkap'];
    $password = $_POST['password'];

    // Validasi input
    if (empty($namalengkap) || empty($password)) {
        $errors[] = "Nama Lengkap dan Password wajib diisi.";
    } else {
        // Cek kredensial pengguna
        $stmt = $conn->prepare("SELECT ID, PASSWORD, PERAN FROM data_user WHERE NAMALENGKAP = ?");
        $stmt->bind_param("s", $namalengkap);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_password, $peran);
            $stmt->fetch();
            
            // Verifikasi password
            if (password_verify($password, $hashed_password)) {
                // Set session dan arahkan ke halaman yang sesuai
                $_SESSION['user_id'] = $id;
                $_SESSION['peran'] = $peran;
                
                switch ($peran) {
                    case 'admin':
                        header("Location: admin.php");
                        break;
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
                $errors[] = "Password salah.";
            }
        } else {
            $errors[] = "Nama Lengkap tidak ditemukan.";
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
    <title>Login</title>
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

        <h2 class="text-center text-2xl font-semibold text-gray-800">Login Pengguna</h2>

        <?php if (!empty($errors)): ?>
            <div class="text-red-500 text-center">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-4">
            <input type="text" id="namalengkap" name="namalengkap" placeholder="Nama Lengkap" value="<?php echo isset($_POST['namalengkap']) ? htmlspecialchars($_POST['namalengkap']) : ''; ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <input type="password" id="password" name="password" placeholder="Password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <input type="submit" value="Login" class="w-full bg-indigo-600 text-white font-semibold py-2 rounded-lg hover:bg-indigo-700 transition duration-300">
        </form>

        
    </div>

</body>
</html>
