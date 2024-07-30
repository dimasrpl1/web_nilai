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

    // Validasi input
    if (empty($namalengkap) || empty($password) || empty($nik) || empty($peran)) {
        $errors[] = "Semua kolom wajib diisi.";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update pengguna
        $stmt = $conn->prepare("UPDATE data_user SET NAMALENGKAP = ?, PASSWORD = ?, NIK = ?, PERAN = ? WHERE ID = ?");
        $stmt->bind_param("ssssi", $namalengkap, $hashed_password, $nik, $peran, $id);

        if ($stmt->execute()) {
            $success_message = "Pengguna berhasil diperbarui.";
        } else {
            $errors[] = "Terjadi kesalahan saat memperbarui pengguna.";
        }
        $stmt->close();
    }
}

// Pagination settings
$limit = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Ambil daftar pengguna dengan pagination
$stmt = $conn->prepare("SELECT ID, NAMALENGKAP, NIK, PERAN FROM data_user LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$stmt->close();

// Get total number of users
$total_result = $conn->query("SELECT COUNT(*) AS total FROM data_user");
$total_row = $total_result->fetch_assoc();
$total_users = $total_row['total'];
$total_pages = ceil($total_users / $limit);

// Ambil nama admin dari sesi
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
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="notification">
    Selamat Datang, <?php echo htmlspecialchars($nama_admin); ?>
</div>

<div class="logo-top-left">
    <img src="logobadag.png" alt="Logo">
</div>

<div class="page-title">Manajemen Pengguna</div>

<div class="riwayat-tugas-container">
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

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Lengkap</th>
                <th>NIK</th>
                <th>Peran</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['ID']); ?></td>
                    <td><?php echo htmlspecialchars($user['NAMALENGKAP']); ?></td>
                    <td><?php echo htmlspecialchars($user['NIK']); ?></td>
                    <td><?php echo htmlspecialchars($user['PERAN']); ?></td>
                    <td>
                        <a href="admin.php?action=delete&id=<?php echo $user['ID']; ?>" class="riwayat-tugas-action-button riwayat-tugas-delete-button" onclick="return confirm('Anda yakin ingin menghapus pengguna ini?')">Hapus</a>
                        <a href="edit_pengguna.php?id=<?php echo $user['ID']; ?>" class="riwayat-tugas-action-button riwayat-tugas-edit-button">Edit</a>
                        <a href="detail_pengguna.php?id=<?php echo $user['ID']; ?>" class="riwayat-tugas-action-button riwayat-tugas-detail-button">Detail</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>



    <a class="logout" href="logout.php">Logout</a>
</div>
<div class="pagination">
        <?php if ($total_pages > 1): ?>
            <ul>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li><a href="admin.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
            </ul>
        <?php endif; ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                const notification = document.querySelector('.notification');
                if (notification) {
                    notification.style.display = 'none';
                }
            }, 3000); // 3 seconds
        });
    </script>
</body>
</html>
