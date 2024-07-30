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
    <link rel="stylesheet" href="edit_pengguna.css">
</head>
<body>
<div class="logo-top-left">
    <img src="logobadag.png" alt="Logo">
</div>

<div class="page-title">Edit Pengguna</div>

    <?php if ($success_message): ?>
        <div class="success-message">
            <p><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="form-box">
            <form action="edit_pengguna.php?id=<?php echo $id; ?>" method="POST">
                <div class="form-group">
                    <label for="namalengkap">Nama Lengkap:</label>
                    <input type="text" id="namalengkap" name="namalengkap" value="<?php echo htmlspecialchars($user['NAMALENGKAP']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($user['PASSWORD']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="nik">NIK:</label>
                    <input type="text" id="nik" name="nik" value="<?php echo htmlspecialchars($user['NIK']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="peran">Peran:</label>
                    <select id="peran" name="peran" required>
                        <option value="admin" <?php echo $user['PERAN'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="guru" <?php echo $user['PERAN'] == 'guru' ? 'selected' : ''; ?>>Guru</option>
                        <option value="siswa" <?php echo $user['PERAN'] == 'siswa' ? 'selected' : ''; ?>>Siswa</option>
                        <option value="orangtua" <?php echo $user['PERAN'] == 'orangtua' ? 'selected' : ''; ?>>Orangtua</option>
                    </select>
                </div>

                <div id="additional-fields">
                    <?php if ($user['PERAN'] == 'guru'): ?>
                        <div class="form-group">
                            <label for="mapel">Mata Pelajaran:</label>
                            <select id="mapel" name="mapel" required>
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
                            <label for="anak">Nama Anak:</label>
                            <select id="anak" name="anak">
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
                            <label for="kelas">Kelas:</label>
                            <select id="kelas" name="kelas">
                                <option value="">Pilih Kelas</option>
                                <option value="XII RPL 1" <?php echo $user['KELAS'] == 'XII RPL 1' ? 'selected' : ''; ?>>XII RPL 1</option>
                                <option value="XII RPL 2" <?php echo $user['KELAS'] == 'XII RPL 2' ? 'selected' : ''; ?>>XII RPL 2</option>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" name="edit" class="submit-button">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <a class="backguru" href="admin.php">Kembali</a>
</body>
</html>
