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

// Handle task update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_task'])) {
    $task_id = $_POST['task_id'];
    $task_title = $_POST['task_title'];
    $due_date = $_POST['due_date'];
    $description = $_POST['description'];
    $subject = $_POST['subject'];

    $stmt = $conn->prepare("UPDATE tugas SET JUDUL_TUGAS = ?, TANGGAL_TUGAS = ?, DESKRIPSI = ?, MAPEL = ? WHERE ID_TUGAS = ?");
    $stmt->bind_param("ssssi", $task_title, $due_date, $description, $subject, $task_id);
    
    if ($stmt->execute()) {
        $success_message = "Tugas berhasil diperbarui.";
    } else {
        $errors[] = "Terjadi kesalahan saat memperbarui tugas.";
    }
    $stmt->close();
}

// Retrieve task details
$task_id = $_GET['id_tugas'];
$stmt = $conn->prepare("SELECT JUDUL_TUGAS, TANGGAL_TUGAS, DESKRIPSI, MAPEL FROM tugas WHERE ID_TUGAS = ?");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$stmt->bind_result($task_title, $due_date, $description, $subject);
$stmt->fetch();
$stmt->close();

// Static list of subjects
$subject_list = [
    "Produktif",
    "PPKN",
    "PAI",
    "Matematika",
    "Bahasa Inggris",
    "PKK",
    "MPKK",
    "Bahasa Indonesia"
];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tugas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <div class="logo-container">
            <img src="logo.png" alt="Logo" class="logo-top-left">
        </div>
        <h2 class="edit-task-heading">Edit Tugas</h2>

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

        <form action="edit_tugas.php?id_tugas=<?php echo $task_id; ?>" method="POST" class="edit-task-form">
            <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task_id); ?>">

            <div class="form-group">
                <label for="task_title">Judul Tugas:</label><br>
                <input type="text" id="task_title" name="task_title" value="<?php echo htmlspecialchars($task_title); ?>" required>
            </div>

            <div class="form-group">
                <label for="due_date">Tanggal Tugas:</label><br>
                <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($due_date); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Deskripsi:</label><br>
                <textarea id="description" name="description" rows="4" cols="50" required><?php echo htmlspecialchars($description); ?></textarea>
            </div>

            <div class="form-group">
                <label for="subject">Mata Pelajaran:</label><br>
                <select id="subject" name="subject" required>
                    <option value="">Pilih Mata Pelajaran</option>
                    <?php foreach ($subject_list as $subject_option): ?>
                        <option value="<?php echo htmlspecialchars($subject_option); ?>"
                            <?php if ($subject_option === $subject) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($subject_option); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group submit-group">
                <input type="submit" name="update_task" value="Perbarui Tugas" class="submit-button">
            </div>
        </form>
        
        <a href="manajemen_tugas.php" class="back-button">Kembali ke Manajemen Tugas</a>
    </div>
</body>
</html>