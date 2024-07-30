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

$success_message = '';
$errors = [];

// Handle hapus pengguna
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Mulai transaksi
    $conn->begin_transaction();
    
    try {
        // Hapus data terkait di tabel nilai_siswa
        $stmt = $conn->prepare("DELETE FROM nilai_siswa WHERE SISWA_ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // Hapus pengguna
        $stmt = $conn->prepare("DELETE FROM data_user WHERE ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // Commit transaksi
        $conn->commit();

        $success_message = "Pengguna berhasil dihapus.";
    } catch (Exception $e) {
        // Rollback transaksi jika ada kesalahan
        $conn->rollback();
        $errors[] = "Terjadi kesalahan saat menghapus pengguna: " . $e->getMessage();
    }
}

// Handle edit pengguna
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $namalengkap = $_POST['namalengkap'];
    $password = $_POST['password'];
    $nik = $_POST['nik'];
    $peran = $_POST['peran'];
    $mapel = $_POST['mapel'] ?? null;
    $anak = $_POST['anak'] ?? null;

    // Validasi input
    if (empty($namalengkap) || empty($password) || empty($nik) || empty($peran)) {
        $errors[] = "Semua kolom wajib diisi.";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update pengguna
        $stmt = $conn->prepare("UPDATE data_user SET NAMALENGKAP = ?, PASSWORD = ?, NIK = ?, PERAN = ?, MAPEL = ?, ANAKSISWA = ? WHERE ID = ?");
        $stmt->bind_param("ssssssi", $namalengkap, $hashed_password, $nik, $peran, $mapel, $anak, $id);

        if ($stmt->execute()) {
            $success_message = "Pengguna berhasil diperbarui.";
        } else {
            $errors[] = "Terjadi kesalahan saat memperbarui pengguna.";
        }
        $stmt->close();
    }



}

// Ambil daftar pengguna
$result = $conn->query("SELECT ID, NAMALENGKAP, NIK, PERAN, MAPEL, ANAKSISWA FROM data_user");

    // Ambil nama orangtua
$nama_admin = null;

// Ambil nama orangtua dari sesi
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
    <title>Manajemen Pengguna</title>
</head>
<body>
<div class="notification">
        Selamat Datang, <?php echo htmlspecialchars($nama_admin); ?>
    </div>
    
    <div class="logo-top-left">
        <img src="logobadag.png" alt="Logo">
    </div>

    <div class="page-title">Manajemen Pengguna</div>

    <?php if ($success_message): ?>
        <div style="color: green;">
            <p><?php echo htmlspecialchars($success_message); ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div style="color: red;">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Lengkap</th>
                <th>NIK</th>
                <th>Peran</th>
                <th>Mata Pelajaran</th>
                <th>Nama Anak</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ID']); ?></td>
                    <td><?php echo htmlspecialchars($row['NAMALENGKAP']); ?></td>
                    <td><?php echo htmlspecialchars($row['NIK']); ?></td>
                    <td><?php echo htmlspecialchars($row['PERAN']); ?></td>
                    <td><?php echo htmlspecialchars($row['MAPEL']); ?></td>
                    <td><?php echo htmlspecialchars($row['ANAKSISWA']); ?></td>
                    <td>
                        <a href="manajemen_pengguna.php?action=delete&id=<?php echo $row['ID']; ?>" onclick="return confirm('Anda yakin ingin menghapus pengguna ini?')">Hapus</a>
                        <a href="edit_pengguna.php?id=<?php echo $row['ID']; ?>">Edit</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <p>
        <?php
        // Menentukan link kembali berdasarkan peran pengguna
        switch ($_SESSION['peran']) {
            case 'admin':
                echo '<a href="admin.php">Kembali ke Beranda Admin</a>';
                break;
            case 'guru':
                echo '<a href="guru.php">Kembali ke Beranda Guru</a>';
                break;
            case 'siswa':
                echo '<a href="siswa.php">Kembali ke Beranda Siswa</a>';
                break;
            case 'orangtua':
                echo '<a href="orangtua.php">Kembali ke Beranda Orangtua</a>';
                break;
        }
        ?>
    </p>
</body>
</html>
