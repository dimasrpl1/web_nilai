<?php
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

$kelas = $_GET['kelas'];
$response = [];

if (!empty($kelas)) {
    $stmt = $conn->prepare("SELECT NAMALENGKAP FROM data_user WHERE KELAS = ? AND PERAN = 'siswa'");
    $stmt->bind_param("s", $kelas);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }
    
    $stmt->close();
}

$conn->close();

echo json_encode($response);
?>
