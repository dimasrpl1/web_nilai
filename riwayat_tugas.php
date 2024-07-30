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
    <link rel="stylesheet" href="riwayat_tugas.css">
</head>
<body>
<div class="logo-top-left">
    <img src="logobadag.png" alt="Logo">
</div>

<div class="page-title">Riwayat Tugas</div>

<div class="riwayat-tugas-container">
    <?php if (!empty($success_message)): ?>
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
        <tr>
            <th>Judul Tugas</th>
            <th>Tanggal Tugas</th>
            <th>Deskripsi</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($tugas_list as $tugas): ?>
            <tr>
                <td><?php echo htmlspecialchars($tugas['JUDUL_TUGAS']); ?></td>
                <td><?php echo htmlspecialchars($tugas['TANGGAL_TUGAS']); ?></td>
                <td><?php echo htmlspecialchars($tugas['DESKRIPSI']); ?></td>
                <td>
                    <form action="riwayat_tugas.php" method="POST" style="display:inline;">
                        <input type="hidden" name="id_tugas" value="<?php echo $tugas['ID_TUGAS']; ?>">
                        <input type="submit" name="delete_tugas" value="Hapus" class="riwayat-tugas-action-button riwayat-tugas-delete-button" onclick="return confirm('Apakah Anda yakin ingin menghapus tugas ini?')">
                    </form>
                    <a href="detail_tugas.php?id_tugas=<?php echo $tugas['ID_TUGAS']; ?>" class="riwayat-tugas-action-button riwayat-tugas-detail-button">Detail</a>
                    <a href="guru_edit_tugas.php?id_tugas=<?php echo $tugas['ID_TUGAS']; ?>" class="riwayat-tugas-action-button riwayat-tugas-edit-button">Edit</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<a class="backguru" href="guru.php">Kembali</a>
</body>
</html>

