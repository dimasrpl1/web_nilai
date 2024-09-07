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

// Pagination settings
$limit = 10; // 10 rows per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Handle search
$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

// Query untuk menghitung total pengguna
$sql_count = "SELECT COUNT(*) as total FROM data_user";
if ($search !== '') {
    $sql_count .= " WHERE NAMALENGKAP LIKE ? OR NIK LIKE ?";
}
$stmt_count = $conn->prepare($sql_count);

if ($search !== '') {
    $search_param = "%" . $search . "%";
    $stmt_count->bind_param("ss", $search_param, $search_param);
}

$stmt_count->execute();
$total_result = $stmt_count->get_result();
$total_row = $total_result->fetch_assoc();
$total_users = $total_row['total'];
$total_pages = ceil($total_users / $limit);

$stmt_count->close();

// Query untuk mendapatkan pengguna dengan pagination dan pencarian
$sql = "SELECT ID, NAMALENGKAP, NIK, PERAN FROM data_user";
if ($search !== '') {
    $sql .= " WHERE NAMALENGKAP LIKE ? OR NIK LIKE ? ";
}
$sql .= " LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);

if ($search !== '') {
    $stmt->bind_param("ssii", $search_param, $search_param, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengguna</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .animate-fadeIn {
            animation: fadeIn 1.5s ease-in-out;
        }

        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .animate-slideUp {
            animation: slideUp 0.8s ease-in-out;
        }
    </style>
</head>
<body class="bg-gradient-to-b from-[#1E0342] to-[#433D8B] min-h-screen flex flex-col items-center p-6">

    <!-- Logo -->
    <div class="absolute top-5 left-5">
        <img src="logobadag.png" alt="Logo" class="w-16 sm:w-24 md:w-32">
    </div>

    <!-- Page Title -->
    <h1 class="text-white text-3xl md:text-4xl font-semibold mb-8 mt-10 animate-fadeIn">Data Pengguna</h1>

    <!-- Search Bar -->
    <form method="GET" action="data_pengguna.php" class="mb-6 w-full max-w-lg sm:max-w-2xl lg:max-w-4xl flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
        <!-- Input Search -->
        <input type="text" name="search" placeholder="Cari berdasarkan Nama atau NIK" value="<?php echo htmlspecialchars($search); ?>"
               class="flex-grow px-4 py-2 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-[#433D8B] transition-all duration-300">

        <!-- Button Cari -->
        <button type="submit" class="bg-white text-[#433D8B] px-5 py-2 rounded-lg shadow-lg hover:bg-[#433D8B] hover:text-white transition duration-300">
            Cari
        </button>

        <!-- Button Refresh -->
        <a href="data_pengguna.php" class="bg-gray-200 text-gray-800 px-5 py-2 rounded-lg shadow-lg hover:bg-gray-400 transition duration-300 text-center">
            Refresh
        </a>
    </form>

    <!-- Data Pengguna Table -->
    <div class="w-full max-w-lg sm:max-w-2xl lg:max-w-4xl bg-white rounded-xl shadow-lg p-4 sm:p-6 animate-slideUp overflow-x-auto">
        <table class="w-full table-auto text-left">
            <thead>
                <tr class="bg-[#433D8B] text-white text-xs sm:text-sm">
                    <th class="px-2 sm:px-4 py-2">ID</th>
                    <th class="px-2 sm:px-4 py-2">Nama Lengkap</th>
                    <th class="px-2 sm:px-4 py-2">NIK</th>
                    <th class="px-2 sm:px-4 py-2">Peran</th>
                    <th class="px-2 sm:px-4 py-2">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-[#f3f4f6] transition-all duration-300 ease-in-out text-xs sm:text-sm">
                            <td class="border px-2 sm:px-4 py-2"><?php echo htmlspecialchars($row['ID']); ?></td>
                            <td class="border px-2 sm:px-4 py-2"><?php echo htmlspecialchars($row['NAMALENGKAP']); ?></td>
                            <td class="border px-2 sm:px-4 py-2"><?php echo htmlspecialchars($row['NIK']); ?></td>
                            <td class="border px-2 sm:px-4 py-2"><?php echo htmlspecialchars($row['PERAN']); ?></td>
                            <td class="border px-2 sm:px-4 py-2">
                                <a href="edit_pengguna.php?id=<?php echo $row['ID']; ?>" class="text-[#433D8B] font-semibold hover:text-[#1E0342] transition duration-300">Edit</a> |
                                <a href="delete_pengguna.php?id=<?php echo $row['ID']; ?>" class="text-red-600 font-semibold hover:text-red-800 transition duration-300">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-gray-500 py-4">Tidak ada pengguna yang ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        <?php if ($total_pages > 1): ?>
            <ul class="flex space-x-2">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li>
                        <a href="data_pengguna.php?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
                           class="px-3 sm:px-4 py-2 bg-white text-[#433D8B] text-xs sm:text-sm rounded-lg shadow-lg hover:bg-[#433D8B] hover:text-white transition duration-300">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- Logout Button -->
    <a href="admin.php" class="fixed bottom-5 right-5 bg-white text-[#433D8B] font-semibold px-3 sm:px-4 py-2 sm:py-3 rounded-xl shadow-lg hover:bg-[#433D8B] hover:text-white transition duration-300">
        Kembali
    </a>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tableRows = document.querySelectorAll('tr');
            tableRows.forEach((row, index) => {
                setTimeout(() => {
                    row.classList.add('animate-slideUp');
                }, index * 100);
            });
        });
    </script>

</body>
</html>
