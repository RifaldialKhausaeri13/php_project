<?php
include 'db_config.php';

$message = "";

// Handle Add/Edit Dosen
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $nip = $_POST['nip'];
        $nama = $_POST['nama'];
        $spesialisasi = $_POST['spesialisasi'];

        if ($action == "add") {
            $stmt = $conn->prepare("INSERT INTO dosen (nip, nama, spesialisasi) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nip, $nama, $spesialisasi);
            if ($stmt->execute()) {
                $message = "Dosen berhasil ditambahkan!";
            } else {
                $message = "Error saat menambah dosen: " . $stmt->error;
            }
            $stmt->close();
        } elseif ($action == "edit") {
            $stmt = $conn->prepare("UPDATE dosen SET nama=?, spesialisasi=? WHERE nip=?");
            $stmt->bind_param("sss", $nama, $spesialisasi, $nip);
            if ($stmt->execute()) {
                $message = "Dosen berhasil diupdate!";
            } else {
                $message = "Error saat mengupdate dosen: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Handle Delete Dosen
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['nip'])) {
    $nip_to_delete = $_GET['nip'];
    $stmt = $conn->prepare("DELETE FROM dosen WHERE nip=?");
    $stmt->bind_param("s", $nip_to_delete);
    if ($stmt->execute()) {
        $message = "Dosen berhasil dihapus!";
    } else {
        $message = "Error saat menghapus dosen: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch all dosen
$result = $conn->query("SELECT * FROM dosen");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Dosen</title>
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
        .form-container input[type="text"] {
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
        <h2>Manajemen Dosen</h2>

        <?php if (!empty($message)): ?>
            <div class="<?php echo (strpos($message, 'Error') === false) ? 'message' : 'error-message'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <h3><?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'Edit Data Dosen' : 'Tambah Dosen Baru'; ?></h3>
            <?php
            $edit_nip = $edit_nama = $edit_spesialisasi = '';
            if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['nip'])) {
                $nip_to_edit = $_GET['nip'];
                $stmt = $conn->prepare("SELECT * FROM dosen WHERE nip=?");
                $stmt->bind_param("s", $nip_to_edit);
                $stmt->execute();
                $edit_result = $stmt->get_result();
                if ($edit_result->num_rows > 0) {
                    $row = $edit_result->fetch_assoc();
                    $edit_nip = $row['nip'];
                    $edit_nama = $row['nama'];
                    $edit_spesialisasi = $row['spesialisasi'];
                }
                $stmt->close();
            }
            ?>
            <form action="dosen.php" method="POST">
                <input type="hidden" name="action" value="<?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'edit' : 'add'; ?>">
                <label for="nip">NIP:</label>
                <input type="text" id="nip" name="nip" value="<?php echo htmlspecialchars($edit_nip); ?>" <?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'readonly' : ''; ?> required><br>
                <label for="nama">Nama:</label>
                <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($edit_nama); ?>" required><br>
                <label for="spesialisasi">Spesialisasi:</label>
                <input type="text" id="spesialisasi" name="spesialisasi" value="<?php echo htmlspecialchars($edit_spesialisasi); ?>"><br>
                <button type="submit"><?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'Update Dosen' : 'Tambah Dosen'; ?></button>
            </form>
        </div>

        <h3>Daftar Dosen Terdaftar</h3>
        <table>
            <thead>
                <tr>
                    <th>NIP</th>
                    <th>Nama</th>
                    <th>Spesialisasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["nip"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["nama"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["spesialisasi"]) . "</td>";
                        echo "<td class='actions'>";
                        echo "<a href='dosen.php?action=edit&nip=" . urlencode($row['nip']) . "'>Edit</a> | ";
                        echo "<a href='dosen.php?action=delete&nip=" . urlencode($row['nip']) . "' onclick='return confirm(\"Yakin ingin menghapus data ini?\")'>Hapus</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>Tidak ada data dosen yang tersedia.</td></tr>";
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