<?php
session_start();

if ($_SESSION['peran'] !== 'admin') {
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

// Handle task deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_tugas'])) {
    $id_tugas = $_POST['id_tugas'];

    // Delete the task
    $stmt = $conn->prepare("DELETE FROM tugas WHERE ID_TUGAS = ?");
    $stmt->bind_param("i", $id_tugas);
    
    if ($stmt->execute()) {
        $success_message = "Tugas berhasil dihapus.";
    } else {
        $errors[] = "Terjadi kesalahan saat menghapus tugas.";
    }
    $stmt->close();
}

// Retrieve all tasks
$tugas_list = [];
$stmt = $conn->prepare("SELECT t.ID_TUGAS, t.JUDUL_TUGAS, t.TANGGAL_TUGAS, t.DESKRIPSI, t.MAPEL 
                        FROM tugas t");
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
    <title>Manajemen Tugas</title>
</head>
<body>
    <h2>Manajemen Tugas</h2>

    <?php if (!empty($success_message)): ?>
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

    <h3>Daftar Tugas</h3>
    <table border="1">
        <tr>
            <th>Mata Pelajaran</th>
            <th>Judul Tugas</th>
            <th>Tenggat Waktu</th>
            <th>Deskripsi</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($tugas_list as $tugas): ?>
            <tr>
                <td><?php echo htmlspecialchars($tugas['MAPEL']); ?></td>
                <td><?php echo htmlspecialchars($tugas['JUDUL_TUGAS']); ?></td>
                <td><?php echo htmlspecialchars($tugas['TANGGAL_TUGAS']); ?></td>
                <td><?php echo htmlspecialchars($tugas['DESKRIPSI']); ?></td>
                <td>
                    <form action="manajemen_tugas.php" method="POST" style="display:inline;">
                        <input type="hidden" name="id_tugas" value="<?php echo $tugas['ID_TUGAS']; ?>">
                        <input type="submit" name="delete_tugas" value="Hapus">
                    </form>
                    <a href="edit_tugas.php?id_tugas=<?php echo $tugas['ID_TUGAS']; ?>">Edit</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <p><a href="admin.php">Kembali ke Beranda Admin</a></p>
</body>
</html>
