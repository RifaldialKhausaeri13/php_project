<?php
include 'db_config.php';

$message = "";

// Fungsi pembantu untuk mengkonversi nilai huruf ke poin angka (untuk perhitungan IPK)
function get_grade_points($grade) {
    switch (strtoupper($grade)) {
        case 'A': return 4.0;
        case 'B': return 3.0;
        case 'C': return 2.0;
        case 'D': return 1.0;
        case 'E': return 0.0;
        default: return 0.0; // Jika nilai tidak valid, anggap 0.0
    }
}

// --- Bagian Penanganan Tambah/Edit Nilai ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $nim = $_POST['nim'];
        $kode_mk = $_POST['kode_mk'];
        $nilai = $_POST['nilai'];
        $tahun_ajaran = $_POST['tahun_ajaran'];
        $semester = $_POST['semester'];

        if ($action == "add") {
            $stmt = $conn->prepare("INSERT INTO nilai (nim, kode_mk, nilai, tahun_ajaran, semester) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nim, $kode_mk, $nilai, $tahun_ajaran, $semester);
            if ($stmt->execute()) {
                $message = "Nilai berhasil ditambahkan!";
            } else {
                $message = "Error saat menambah nilai: " . $stmt->error;
            }
            $stmt->close();
        } elseif ($action == "edit") {
            $id_nilai = $_POST['id_nilai'];
            $stmt = $conn->prepare("UPDATE nilai SET nim=?, kode_mk=?, nilai=?, tahun_ajaran=?, semester=? WHERE id_nilai=?");
            $stmt->bind_param("sssssi", $nim, $kode_mk, $nilai, $tahun_ajaran, $semester, $id_nilai); // 'i' untuk integer id_nilai
            if ($stmt->execute()) {
                $message = "Nilai berhasil diupdate!";
            } else {
                $message = "Error saat mengupdate nilai: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// --- Bagian Penanganan Hapus Nilai ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id_nilai'])) {
    $id_nilai_to_delete = $_GET['id_nilai'];
    $stmt = $conn->prepare("DELETE FROM nilai WHERE id_nilai=?");
    $stmt->bind_param("i", $id_nilai_to_delete);
    if ($stmt->execute()) {
        $message = "Nilai berhasil dihapus!";
    } else {
        $message = "Error saat menghapus nilai: " . $stmt->error;
    }
    $stmt->close();
}

// --- Mengambil Semua Data Nilai dengan Nama Mahasiswa dan Mata Kuliah ---
$sql_grades = "SELECT n.id_nilai, m.nim, m.nama AS nama_mahasiswa, mk.nama_mk, mk.sks, n.nilai, n.tahun_ajaran, n.semester
               FROM nilai n
               JOIN mahasiswa m ON n.nim = m.nim
               JOIN mata_kuliah mk ON n.kode_mk = mk.kode_mk
               ORDER BY m.nama, n.tahun_ajaran, n.semester, mk.nama_mk";
$result_grades = $conn->query($sql_grades);

// Mengambil data mahasiswa dan mata kuliah untuk dropdown di form
$students_result = $conn->query("SELECT nim, nama FROM mahasiswa ORDER BY nama");
$courses_result = $conn->query("SELECT kode_mk, nama_mk FROM mata_kuliah ORDER BY nama_mk");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Nilai dan IPK</title>
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
        .gpa-section {
            margin-top: 40px;
            padding: 25px;
            background-color: #e6f7ff;
            border: 1px solid #b3e0ff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .gpa-section h3 { margin-top: 0; color: #0056b3; margin-bottom: 20px; }
        .gpa-result { font-size: 1.3em; font-weight: bold; color: #004085; text-align: center; margin-top: 20px; }
        .gpa-section select { width: calc(100% - 24px); }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manajemen Nilai dan IPK</h2>

        <?php if (!empty($message)): ?>
            <div class="<?php echo (strpos($message, 'Error') === false) ? 'message' : 'error-message'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <h3><?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'Edit Data Nilai' : 'Tambah Nilai Mahasiswa'; ?></h3>
            <?php
            $edit_id_nilai = $edit_nim = $edit_kode_mk = $edit_nilai = $edit_tahun_ajaran = $edit_semester = '';
            if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id_nilai'])) {
                $id_nilai_to_edit = $_GET['id_nilai'];
                $stmt = $conn->prepare("SELECT * FROM nilai WHERE id_nilai=?");
                $stmt->bind_param("i", $id_nilai_to_edit);
                $stmt->execute();
                $edit_result = $stmt->get_result();
                if ($edit_result->num_rows > 0) {
                    $row = $edit_result->fetch_assoc();
                    $edit_id_nilai = $row['id_nilai'];
                    $edit_nim = $row['nim'];
                    $edit_kode_mk = $row['kode_mk'];
                    $edit_nilai = $row['nilai'];
                    $edit_tahun_ajaran = $row['tahun_ajaran'];
                    $edit_semester = $row['semester'];
                }
                $stmt->close();
            }
            ?>
            <form action="nilai.php" method="POST">
                <input type="hidden" name="action" value="<?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'edit' : 'add'; ?>">
                <input type="hidden" name="id_nilai" value="<?php echo htmlspecialchars($edit_id_nilai); ?>">

                <label for="nim">Mahasiswa:</label>
                <select id="nim" name="nim" required>
                    <option value="">-- Pilih Mahasiswa --</option>
                    <?php
                    $students_result->data_seek(0); // Reset pointer untuk dropdown mahasiswa
                    while ($row_student = $students_result->fetch_assoc()) {
                        $selected = ($row_student['nim'] == $edit_nim) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($row_student['nim']) . "' $selected>" . htmlspecialchars($row_student['nama']) . " (" . htmlspecialchars($row_student['nim']) . ")</option>";
                    }
                    ?>
                </select><br>

                <label for="kode_mk">Mata Kuliah:</label>
                <select id="kode_mk" name="kode_mk" required>
                    <option value="">-- Pilih Mata Kuliah --</option>
                    <?php
                    $courses_result->data_seek(0); // Reset pointer untuk dropdown mata kuliah
                    while ($row_course = $courses_result->fetch_assoc()) {
                        $selected = ($row_course['kode_mk'] == $edit_kode_mk) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($row_course['kode_mk']) . "' $selected>" . htmlspecialchars($row_course['nama_mk']) . " (" . htmlspecialchars($row_course['kode_mk']) . ")</option>";
                    }
                    ?>
                </select><br>

                <label for="nilai">Nilai (A-E):</label>
                <input type="text" id="nilai" name="nilai" value="<?php echo htmlspecialchars($edit_nilai); ?>" maxlength="1" required><br>

                <label for="tahun_ajaran">Tahun Ajaran (misal: 2024/2025):</label>
                <input type="text" id="tahun_ajaran" name="tahun_ajaran" value="<?php echo htmlspecialchars($edit_tahun_ajaran); ?>" required><br>

                <label for="semester">Semester (misal: Ganjil/Genap):</label>
                <input type="text" id="semester" name="semester" value="<?php echo htmlspecialchars($edit_semester); ?>" required><br>

                <button type="submit"><?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'Update Nilai' : 'Tambah Nilai'; ?></button>
            </form>
        </div>

        <h3>Daftar Nilai Mahasiswa</h3>
        <table>
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Nama Mahasiswa</th>
                    <th>Mata Kuliah</th>
                    <th>SKS</th>
                    <th>Nilai</th>
                    <th>Tahun Ajaran</th>
                    <th>Semester</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_grades->num_rows > 0) {
                    while($row = $result_grades->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["nim"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["nama_mahasiswa"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["nama_mk"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["sks"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["nilai"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["tahun_ajaran"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["semester"]) . "</td>";
                        echo "<td class='actions'>";
                        echo "<a href='nilai.php?action=edit&id_nilai=" . urlencode($row['id_nilai']) . "'>Edit</a> | ";
                        echo "<a href='nilai.php?action=delete&id_nilai=" . urlencode($row['id_nilai']) . "' onclick='return confirm(\"Yakin ingin menghapus nilai ini?\")'>Hapus</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8'>Belum ada data nilai yang tercatat.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div class="gpa-section">
            <h3>Hitung Indeks Prestasi Kumulatif (IPK) Mahasiswa</h3>
            <form action="" method="GET">
                <label for="gpa_nim">Pilih Mahasiswa:</label>
                <select id="gpa_nim" name="gpa_nim" required>
                    <option value="">-- Pilih Mahasiswa --</option>
                    <?php
                    $students_result->data_seek(0); // Reset pointer lagi untuk dropdown IPK
                    while ($row_student = $students_result->fetch_assoc()) {
                        $selected = (isset($_GET['gpa_nim']) && $row_student['nim'] == $_GET['gpa_nim']) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($row_student['nim']) . "' $selected>" . htmlspecialchars($row_student['nama']) . " (" . htmlspecialchars($row_student['nim']) . ")</option>";
                    }
                    ?>
                </select><br>
                <button type="submit">Hitung IPK</button>
            </form>

            <?php
            // Logika perhitungan IPK
            if (isset($_GET['gpa_nim']) && !empty($_GET['gpa_nim'])) {
                $gpa_nim = $_GET['gpa_nim'];
                $sql_gpa = "SELECT n.nilai, mk.sks
                            FROM nilai n
                            JOIN mata_kuliah mk ON n.kode_mk = mk.kode_mk
                            WHERE n.nim = ?";
                $stmt_gpa = $conn->prepare($sql_gpa);
                $stmt_gpa->bind_param("s", $gpa_nim);
                $stmt_gpa->execute();
                $result_gpa = $stmt_gpa->get_result();

                $total_sks = 0;
                $total_weighted_points = 0;
                $student_name_gpa = "";

                if ($result_gpa->num_rows > 0) {
                    // Ambil nama mahasiswa untuk ditampilkan
                    $stmt_student_name = $conn->prepare("SELECT nama FROM mahasiswa WHERE nim = ?");
                    $stmt_student_name->bind_param("s", $gpa_nim);
                    $stmt_student_name->execute();
                    $result_student_name = $stmt_student_name->get_result();
                    if ($row_student_name = $result_student_name->fetch_assoc()) {
                        $student_name_gpa = $row_student_name['nama'];
                    }
                    $stmt_student_name->close();

                    while ($row_gpa = $result_gpa->fetch_assoc()) {
                        $grade_points = get_grade_points($row_gpa['nilai']);
                        $sks = $row_gpa['sks'];
                        $total_weighted_points += ($grade_points * $sks);
                        $total_sks += $sks;
                    }

                    if ($total_sks > 0) {
                        $ipk = $total_weighted_points / $total_sks;
                        echo "<p class='gpa-result'>IPK untuk " . htmlspecialchars($student_name_gpa) . " (" . htmlspecialchars($gpa_nim) . ") adalah: <strong>" . number_format($ipk, 2) . "</strong></p>";
                    } else {
                        echo "<p class='gpa-result'>Tidak ada SKS yang tercatat untuk mahasiswa ini.</p>";
                    }
                } else {
                    echo "<p class='gpa-result'>Tidak ada nilai tercatat untuk mahasiswa ini.</p>";
                }
                $stmt_gpa->close();
            }
            ?>
        </div>

        <div class="back-link">
            <a href="index.php">Kembali ke Menu Utama</a>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>