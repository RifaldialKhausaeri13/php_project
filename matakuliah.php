<?php
include 'db_config.php';

$message = "";

// Handle Add/Edit Mata Kuliah
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $kode_mk = $_POST['kode_mk'];
        $nama_mk = $_POST['nama_mk'];
        $sks = $_POST['sks'];
        $nip_dosen = $_POST['nip_dosen']; // Dapat null jika dipilih "-- Pilih Dosen --"

        if ($action == "add") {
            $stmt = $conn->prepare("INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, nip_dosen) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssis", $kode_mk, $nama_mk, $sks, $nip_dosen); // 's' untuk string, 'i' untuk integer
            if ($stmt->execute()) {
                $message = "Mata Kuliah berhasil ditambahkan!";
            } else {
                $message = "Error saat menambah Mata Kuliah: " . $stmt->error;
            }
            $stmt->close();
        } elseif ($action == "edit") {
            $stmt = $conn->prepare("UPDATE mata_kuliah SET nama_mk=?, sks=?, nip_dosen=? WHERE kode_mk=?");
            $stmt->bind_param("siss", $nama_mk, $sks, $nip_dosen, $kode_mk);
            if ($stmt->execute()) {
                $message = "Mata Kuliah berhasil diupdate!";
            } else {
                $message = "Error saat mengupdate Mata Kuliah: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Handle Delete Mata Kuliah
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['kode_mk'])) {
    $kode_mk_to_delete = $_GET['kode_mk'];
    $stmt = $conn->prepare("DELETE FROM mata_kuliah WHERE kode_mk=?");
    $stmt->bind_param("s", $kode_mk_to_delete);
    if ($stmt->execute()) {
        $message = "Mata Kuliah berhasil dihapus!";
    } else {
        $message = "Error saat menghapus Mata Kuliah: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch all mata kuliah with dosen name
$sql_matkul = "SELECT mk.kode_mk, mk.nama_mk, mk.sks, mk.nip_dosen, d.nama AS nama_dosen
               FROM mata_kuliah mk
               LEFT JOIN dosen d ON mk.nip_dosen = d.nip
               ORDER BY mk.nama_mk";
$result_matkul = $conn->query($sql_matkul);

// Fetch all dosen for dropdown
$dosen_result = $conn->query("SELECT nip, nama FROM dosen ORDER BY nama");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Mata Kuliah</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f7f6; }
        .container { max-width: 900px; margin: auto; background: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 20px; }
        .message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        .error-message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
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
        .form-container input[type="text"], .form-container input[type="number"], .form-container select {
            width: calc(100% - 24px);
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            box-sizing: border-box;
        }
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
        <h2>Manajemen Mata Kuliah</h2>

        <?php if (!empty($message)): ?>
            <div class="<?php echo (strpos($message, 'Error') === false) ? 'message' : 'error-message'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <h3><?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'Edit Data Mata Kuliah' : 'Tambah Mata Kuliah Baru'; ?></h3>
            <?php
            $edit_kode_mk = $edit_nama_mk = $edit_sks = $edit_nip_dosen = '';
            if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['kode_mk'])) {
                $kode_mk_to_edit = $_GET['kode_mk'];
                $stmt = $conn->prepare("SELECT * FROM mata_kuliah WHERE kode_mk=?");
                $stmt->bind_param("s", $kode_mk_to_edit);
                $stmt->execute();
                $edit_result = $stmt->get_result();
                if ($edit_result->num_rows > 0) {
                    $row = $edit_result->fetch_assoc();
                    $edit_kode_mk = $row['kode_mk'];
                    $edit_nama_mk = $row['nama_mk'];
                    $edit_sks = $row['sks'];
                    $edit_nip_dosen = $row['nip_dosen'];
                }
                $stmt->close();
            }
            ?>
            <form action="matakuliah.php" method="POST">
                <input type="hidden" name="action" value="<?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'edit' : 'add'; ?>">
                <label for="kode_mk">Kode Mata Kuliah:</label>
                <input type="text" id="kode_mk" name="kode_mk" value="<?php echo htmlspecialchars($edit_kode_mk); ?>" <?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'readonly' : ''; ?> required><br>
                <label for="nama_mk">Nama Mata Kuliah:</label>
                <input type="text" id="nama_mk" name="nama_mk" value="<?php echo htmlspecialchars($edit_nama_mk); ?>" required><br>
                <label for="sks">SKS:</label>
                <input type="number" id="sks" name="sks" value="<?php echo htmlspecialchars($edit_sks); ?>" required min="1"><br>
                <label for="nip_dosen">Dosen Pengampu:</label>
                <select id="nip_dosen" name="nip_dosen">
                    <option value="">-- Pilih Dosen --</option>
                    <?php
                    $dosen_result->data_seek(0); // Reset pointer for dropdown
                    while ($row_dosen = $dosen_result->fetch_assoc()) {
                        $selected = ($row_dosen['nip'] == $edit_nip_dosen) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($row_dosen['nip']) . "' $selected>" . htmlspecialchars($row_dosen['nama']) . "</option>";
                    }
                    ?>
                </select><br>
                <button type="submit"><?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'Update Mata Kuliah' : 'Tambah Mata Kuliah'; ?></button>
            </form>
        </div>

        <h3>Daftar Mata Kuliah Terdaftar</h3>
        <table>
            <thead>
                <tr>
                    <th>Kode MK</th>
                    <th>Nama MK</th>
                    <th>SKS</th>
                    <th>Dosen Pengampu</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_matkul->num_rows > 0) {
                    while($row = $result_matkul->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["kode_mk"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["nama_mk"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["sks"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["nama_dosen"] ?: 'Belum Ditentukan') . "</td>"; // Tampilkan nama dosen atau pesan jika null
                        echo "<td class='actions'>";
                        echo "<a href='matakuliah.php?action=edit&kode_mk=" . urlencode($row['kode_mk']) . "'>Edit</a> | ";
                        echo "<a href='matakuliah.php?action=delete&kode_mk=" . urlencode($row['kode_mk']) . "' onclick='return confirm(\"Yakin ingin menghapus data ini?\")'>Hapus</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Tidak ada data mata kuliah yang tersedia.</td></tr>";
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
$conn->close();
?>