<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nilai_siswa";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$kelas = $_GET['kelas'];

$stmt = $conn->prepare("SELECT NAMALENGKAP FROM data_user WHERE PERAN = 'siswa' AND KELAS = ?");
$stmt->bind_param("s", $kelas);
$stmt->execute();
$result = $stmt->get_result();

$siswa = [];
while ($row = $result->fetch_assoc()) {
    $siswa[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($siswa);
?>
