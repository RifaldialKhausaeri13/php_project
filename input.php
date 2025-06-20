<?php
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nim = $_POST['nim'];
    $kode_mk = $_POST['kode_mk'];
    $nip = $_POST['nip'];
    $nilai = $_POST['nilai'];

    // Insert data ke tabel kuliah
    $stmt = $conn->prepare("INSERT INTO kuliah (nim, kode_mk, nip, nilai) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nim, $kode_mk, $nip, $nilai);
    if ($stmt->execute()) {
        // Redirect ke report.php dan langsung tampilkan data yg baru ditambahkan
        header("Location: report.php?nim_mahasiswa=$nim&kode_mk=$kode_mk&nip_dosen=$nip");
        exit();
    } else {
        echo "Gagal menambahkan data: " . $stmt->error;
    }
    $stmt->close();
}
?>
