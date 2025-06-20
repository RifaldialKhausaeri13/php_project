<?php
include 'db_config.php';

$message = "";
$report_results = null;

// Ambil data dropdown
$dosen_result = $conn->query("SELECT nip, nama FROM dosen ORDER BY nama");
$matkul_result = $conn->query("SELECT kode_mk, nama_mk FROM mata_kuliah ORDER BY nama_mk");
$mahasiswa_result = $conn->query("SELECT nim, nama FROM mahasiswa ORDER BY nama");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nip_dosen = $_POST['nip_dosen'] ?? '';
    $kode_mk = $_POST['kode_mk'] ?? '';
    $nim_mahasiswa = $_POST['nim_mahasiswa'] ?? '';

    // Pilihan kombinasi berdasarkan input
    if (!empty($nip_dosen) && !empty($kode_mk) && !empty($nim_mahasiswa)) {
        $stmt = $conn->prepare("CALL get_laporan_by_dosen_matkul_mahasiswa(?, ?, ?)");
        $stmt->bind_param("sss", $nip_dosen, $kode_mk, $nim_mahasiswa);
    } elseif (!empty($nip_dosen) && !empty($kode_mk)) {
        $stmt = $conn->prepare("CALL get_mahasiswa_per_dosen_matkul(?, ?)");
        $stmt->bind_param("ss", $nip_dosen, $kode_mk);
    } elseif (!empty($nip_dosen)) {
        $stmt = $conn->prepare("CALL get_mahasiswa_per_dosen(?)");
        $stmt->bind_param("s", $nip_dosen);
    } elseif (!empty($kode_mk)) {
        $stmt = $conn->prepare("CALL get_mahasiswa_per_matkul(?)");
        $stmt->bind_param("s", $kode_mk);
    } elseif (!empty($nim_mahasiswa)) {
        $stmt = $conn->prepare("CALL get_laporan_per_mahasiswa(?)");
        $stmt->bind_param("s", $nim_mahasiswa);
    } else {
        $message = "Silakan pilih minimal satu filter untuk menampilkan laporan.";
    }

    // Eksekusi prosedur
    if (isset($stmt)) {
        if ($stmt->execute()) {
            $report_results = $stmt->get_result();
            if (!$report_results) {
                $message = "Gagal mengambil hasil laporan: " . $conn->error;
            }
        } else {
            $message = "Gagal menjalankan laporan: " . $stmt->error;
        }
        $stmt->close();

        // Bersihkan hasil untuk prosedur selanjutnya
        while ($conn->more_results() && $conn->next_result()) {
            if ($res = $conn->store_result()) {
                $res->free();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Mahasiswa</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f7f6; }
        .container { max-width: 900px; margin: auto; background: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 20px; }
        .message { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 25px; }
        th, td { border: 1px solid #e0e0e0; padding: 12px; text-align: left; }
        th { background-color: #e9ecef; color: #333; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        tr:hover { background-color: #f1f1f1; }
        .form-container { background: #fdfdfd; padding: 25px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border: 1px solid #e0e0e0; }
        .form-container label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        .form-container select {
            width: calc(100% - 24px);
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .form-container button {
            background-color: #007bff;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background-color 0.3s ease;
        }
        .form-container button:hover { background-color: #0056b3; }
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
    <h2>Laporan Mahasiswa per Dosen/Mata Kuliah</h2>

    <?php if (!empty($message)): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="form-container">
        <form action="report.php" method="POST">
            <label for="nip_dosen">Pilih Dosen:</label>
            <select id="nip_dosen" name="nip_dosen">
                <option value="">-- Semua Dosen --</option>
                <?php
                $dosen_result->data_seek(0);
                while ($row = $dosen_result->fetch_assoc()) {
                    $selected = (isset($_POST['nip_dosen']) && $row['nip'] == $_POST['nip_dosen']) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($row['nip']) . "' $selected>" . htmlspecialchars($row['nama']) . "</option>";
                }
                ?>
            </select>

            <label for="kode_mk">Pilih Mata Kuliah:</label>
            <select id="kode_mk" name="kode_mk">
                <option value="">-- Semua Mata Kuliah --</option>
                <?php
                $matkul_result->data_seek(0);
                while ($row = $matkul_result->fetch_assoc()) {
                    $selected = (isset($_POST['kode_mk']) && $row['kode_mk'] == $_POST['kode_mk']) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($row['kode_mk']) . "' $selected>" . htmlspecialchars($row['nama_mk']) . "</option>";
                }
                ?>
            </select>

            <label for="nim_mahasiswa">Pilih Mahasiswa:</label>
            <select id="nim_mahasiswa" name="nim_mahasiswa">
                <option value="">-- Semua Mahasiswa --</option>
                <?php
                $mahasiswa_result->data_seek(0);
                while ($row = $mahasiswa_result->fetch_assoc()) {
                    $selected = (isset($_POST['nim_mahasiswa']) && $row['nim'] == $_POST['nim_mahasiswa']) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($row['nim']) . "' $selected>" . htmlspecialchars($row['nama']) . "</option>";
                }
                ?>
            </select>

            <button type="submit">Tampilkan Laporan</button>
        </form>
    </div>

    <?php if ($report_results): ?>
        <h3>Hasil Laporan</h3>
        <?php if ($report_results->num_rows > 0): ?>
            <table>
                <thead>
                <tr>
                    <th>NIM</th>
                    <th>Nama Mahasiswa</th>
                    <th>Nama Mata Kuliah</th>
                    <th>Nama Dosen</th>
                    <th>Nilai</th>
                </tr>
                </thead>
                <tbody>
                <?php while($row = $report_results->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nim']); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_mahasiswa']); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_mk'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_dosen'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['nilai'] ?? ''); ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: #dc3545;">Tidak ada data mahasiswa untuk kriteria yang dipilih.</p>
        <?php endif; ?>
    <?php endif; ?>

    <div class="back-link">
        <a href="index.php">Kembali ke Menu Utama</a>
    </div>
</div>
</body>
</html>

<?php
$conn->close();
?>
