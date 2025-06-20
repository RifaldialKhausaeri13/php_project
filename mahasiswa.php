<?php
// Memasukkan file konfigurasi database
include 'db_config.php';

$message = ""; // Variabel untuk menyimpan pesan sukses/error

// --- Bagian Penanganan Tambah/Edit Mahasiswa ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $action = $_POST['action']; // Aksi: 'add' (tambah) atau 'edit' (ubah)

        // Mengambil data dari form
        $nim = $_POST['nim'];
        $nama = $_POST['nama'];
        $jurusan = $_POST['jurusan'];
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $alamat = $_POST['alamat'];

        if ($action == "add") {
            // Query untuk menambah data mahasiswa baru
            $stmt = $conn->prepare("INSERT INTO mahasiswa (nim, nama, jurusan, tanggal_lahir, alamat) VALUES (?, ?, ?, ?, ?)");
            // 'sssss' berarti semua parameter adalah string
            $stmt->bind_param("sssss", $nim, $nama, $jurusan, $tanggal_lahir, $alamat);
            if ($stmt->execute()) {
                $message = "Mahasiswa berhasil ditambahkan!";
            } else {
                $message = "Error saat menambah mahasiswa: " . $stmt->error;
            }
            $stmt->close();
        } elseif ($action == "edit") {
            // Query untuk mengubah data mahasiswa
            $stmt = $conn->prepare("UPDATE mahasiswa SET nama=?, jurusan=?, tanggal_lahir=?, alamat=? WHERE nim=?");
            // 'sssss' berarti semua parameter adalah string (urutan sesuai UPDATE)
            $stmt->bind_param("sssss", $nama, $jurusan, $tanggal_lahir, $alamat, $nim);
            if ($stmt->execute()) {
                $message = "Mahasiswa berhasil diupdate!";
            } else {
                $message = "Error saat mengupdate mahasiswa: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// --- Bagian Penanganan Hapus Mahasiswa ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['nim'])) {
    $nim_to_delete = $_GET['nim']; // NIM mahasiswa yang akan dihapus
    $stmt = $conn->prepare("DELETE FROM mahasiswa WHERE nim=?");
    $stmt->bind_param("s", $nim_to_delete); // 's' berarti parameter adalah string
    if ($stmt->execute()) {
        $message = "Mahasiswa berhasil dihapus!";
    } else {
        $message = "Error saat menghapus mahasiswa: " . $stmt->error;
    }
    $stmt->close();
}

// --- Mengambil Semua Data Mahasiswa untuk Ditampilkan ---
$result = $conn->query("SELECT * FROM mahasiswa");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Mahasiswa</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f7f6; }
        .container { max-width: 900px; margin: auto; background: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 20px; }
        .message {
            background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;
        }
        .error-message {
            background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 25px; }
        th, td { border: 1px solid #e0e0e0; padding: 12px; text-align: left; }
        th { background-color: #e9ecef; color: #333; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        tr:hover { background-color: #f1f1f1; }
        .actions a { margin-right: 10px; text-decoration: none; color: #007bff; font-weight: bold; }
        .actions a:hover { text-decoration: underline; }
        .form-container { background: #fdfdfd; padding: 25px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border: 1px solid #e0e0e0; }
        .form-container h3 { margin-top: 0; color: #34495e; margin-bottom: 20px; }
        .form-container label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        .form-container input[type="text"], .form-container input[type="date"], .form-container textarea {
            width: calc(100% - 24px); /* Kurangi padding dan border */
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            box-sizing: border-box; /* Pastikan padding tidak menambah lebar */
        }
        .form-container textarea { resize: vertical; min-height: 60px; }
        .form-container button {
            background-color: #28a745;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background-color 0.3s ease;
        }
        .form-container button:hover { background-color: #218838; }
        .back-link { display: block; text-align: center; margin-top: 30px; }
        .back-link a {
            color: #6c757d;
            text-decoration: none;
            padding: 8px 15px;
            border: 1px solid #6c757d;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
        }
        .back-link a:hover { background-color: #6c757d; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manajemen Mahasiswa</h2>

        <?php if (!empty($message)): ?>
            <div class="<?php echo (strpos($message, 'Error') === false) ? 'message' : 'error-message'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <h3><?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'Edit Data Mahasiswa' : 'Tambah Mahasiswa Baru'; ?></h3>
            <?php
            $edit_nim = $edit_nama = $edit_jurusan = $edit_tanggal_lahir = $edit_alamat = '';
            if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['nim'])) {
                $nim_to_edit = $_GET['nim'];
                $stmt = $conn->prepare("SELECT * FROM mahasiswa WHERE nim=?");
                $stmt->bind_param("s", $nim_to_edit);
                $stmt->execute();
                $edit_result = $stmt->get_result();
                if ($edit_result->num_rows > 0) {
                    $row = $edit_result->fetch_assoc();
                    $edit_nim = $row['nim'];
                    $edit_nama = $row['nama'];
                    $edit_jurusan = $row['jurusan'];
                    $edit_tanggal_lahir = $row['tanggal_lahir'];
                    $edit_alamat = $row['alamat'];
                }
                $stmt->close();
            }
            ?>
            <form action="mahasiswa.php" method="POST">
                <input type="hidden" name="action" value="<?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'edit' : 'add'; ?>">
                <label for="nim">NIM:</label>
                <input type="text" id="nim" name="nim" value="<?php echo htmlspecialchars($edit_nim); ?>" <?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'readonly' : ''; ?> required><br>
                <label for="nama">Nama:</label>
                <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($edit_nama); ?>" required><br>
                <label for="jurusan">Jurusan:</label>
                <input type="text" id="jurusan" name="jurusan" value="<?php echo htmlspecialchars($edit_jurusan); ?>"><br>
                <label for="tanggal_lahir">Tanggal Lahir:</label>
                <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo htmlspecialchars($edit_tanggal_lahir); ?>"><br>
                <label for="alamat">Alamat:</label>
                <textarea id="alamat" name="alamat"><?php echo htmlspecialchars($edit_alamat); ?></textarea><br>
                <button type="submit"><?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'Update Mahasiswa' : 'Tambah Mahasiswa'; ?></button>
            </form>
        </div>

        <h3>Daftar Mahasiswa Terdaftar</h3>
        <table>
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Jurusan</th>
                    <th>Tanggal Lahir</th>
                    <th>Alamat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["nim"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["nama"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["jurusan"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["tanggal_lahir"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["alamat"]) . "</td>";
                        echo "<td class='actions'>";
                        echo "<a href='mahasiswa.php?action=edit&nim=" . urlencode($row['nim']) . "'>Edit</a> | ";
                        echo "<a href='mahasiswa.php?action=delete&nim=" . urlencode($row['nim']) . "' onclick='return confirm(\"Yakin ingin menghapus data ini?\")'>Hapus</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>Tidak ada data mahasiswa yang tersedia.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <div class="back-link">
            <a href="index.php">Kembali ke Menu Utama</a>
        </div>
    </div>
</body>
</html>

<?php
// Menutup koneksi database setelah selesai digunakan
$conn->close();
?>