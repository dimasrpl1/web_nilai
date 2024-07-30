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
    <link rel="stylesheet" href="detail_tugas.css">
</head>
<body>
<div class="logo-top-left">
    <img src="logobadag.png" alt="Logo">
</div>

<div class="page-title">Detail Tugas</div>

<div class="detail-tugas-container">

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

    <div class="riwayat-tugas-container">
        <table>
            <tr>
                <th>Nama Siswa</th>
                <th>Nilai</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($siswa_list as $siswa): ?>
                <tr>
                    <td><?php echo htmlspecialchars($siswa['NAMALENGKAP'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($siswa['NILAI'] ?? ''); ?></td>
                    <td>
                        <form action="detail_tugas.php?id_tugas=<?php echo $id_tugas; ?>" method="POST" style="display:flex; gap: 10px;">
                            <input type="hidden" name="id_siswa" value="<?php echo $siswa['ID_SISWA']; ?>">
                            <input type="number" name="nilai" value="<?php echo htmlspecialchars($siswa['NILAI'] ?? ''); ?>" required min="0" max="100">
                            <input type="submit" name="submit_nilai" value="Simpan Nilai" class="button-primary">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="pagination">
        <?php if ($total_pages > 1): ?>
            <ul>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li><a href="detail_tugas.php?id_tugas=<?php echo $id_tugas; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
            </ul>
        <?php endif; ?>
    </div>
 </div>

    <a class="backguru" href="riwayat_tugas.php">Kembali</a>
</body>
</html>
