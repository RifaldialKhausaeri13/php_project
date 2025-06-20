<?php
// seed_data.php
include 'db_config.php'; // Memasukkan konfigurasi database

echo "<h1>Menambahkan Data Awal (Seeding Database)</h1>";

// Fungsi untuk eksekusi query SQL dengan pesan
function execute_sql($conn, $sql, $success_msg, $error_prefix) {
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color: green;'>✅ " . $success_msg . "</p>";
    } else {
        echo "<p style='color: red;'>❌ " . $error_prefix . ": " . $conn->error . "</p>";
    }
}

// --- 1. Tambah Dosen ---
echo "<h2>Menambahkan Data Dosen...</h2>";
$nip_dr_siti = 'D001';
$nama_dr_siti = 'Dr. Siti Rahayu';
$spesialisasi_dr_siti = 'Jaringan Komputer';

$check_dosen = $conn->query("SELECT nip FROM dosen WHERE nip = '$nip_dr_siti'");
if ($check_dosen->num_rows == 0) {
    $sql_dosen = "INSERT INTO dosen (nip, nama, spesialisasi) VALUES ('$nip_dr_siti', '$nama_dr_siti', '$spesialisasi_dr_siti')";
    execute_sql($conn, $sql_dosen, "Dosen Dr. Siti Rahayu berhasil ditambahkan.", "Gagal menambah dosen Dr. Siti Rahayu");
} else {
    echo "<p style='color: orange;'>⚠️ Dosen Dr. Siti Rahayu sudah ada, melewati penambahan.</p>";
}

// --- 2. Tambah Mahasiswa ---
echo "<h2>Menambahkan Data Mahasiswa...</h2>";
$nim_dani = 'M001';
$nama_dani = 'Dani A Hermawan';
$jurusan_dani = 'Teknik Informatika';
$tanggal_lahir_dani = '2003-05-15';
$alamat_dani = 'Jl. Anggrek No. 10, Bandung';

$check_mahasiswa = $conn->query("SELECT nim FROM mahasiswa WHERE nim = '$nim_dani'");
if ($check_mahasiswa->num_rows == 0) {
    $sql_mahasiswa = "INSERT INTO mahasiswa (nim, nama, jurusan, tanggal_lahir, alamat) VALUES ('$nim_dani', '$nama_dani', '$jurusan_dani', '$tanggal_lahir_dani', '$alamat_dani')";
    execute_sql($conn, $sql_mahasiswa, "Mahasiswa Dani A Hermawan berhasil ditambahkan.", "Gagal menambah mahasiswa Dani A Hermawan");
} else {
    echo "<p style='color: orange;'>⚠️ Mahasiswa Dani A Hermawan sudah ada, melewati penambahan.</p>";
}

// --- 3. Tambah Mata Kuliah ---
echo "<h2>Menambahkan Data Mata Kuliah...</h2>";
$kode_mk_jarkom = 'MK001';
$nama_mk_jarkom = 'Jaringan Komputer';
$sks_jarkom = 3;

$check_matkul = $conn->query("SELECT kode_mk FROM mata_kuliah WHERE kode_mk = '$kode_mk_jarkom'");
if ($check_matkul->num_rows == 0) {
    // Asumsikan NIP dosen sudah ada (D001 dari atas)
    $sql_matkul = "INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, nip_dosen) VALUES ('$kode_mk_jarkom', '$nama_mk_jarkom', $sks_jarkom, '$nip_dr_siti')";
    execute_sql($conn, $sql_matkul, "Mata Kuliah Jaringan Komputer berhasil ditambahkan.", "Gagal menambah Mata Kuliah Jaringan Komputer");
} else {
    echo "<p style='color: orange;'>⚠️ Mata Kuliah Jaringan Komputer sudah ada, melewati penambahan.</p>";
}

// --- 4. Tambah Nilai (yang menghubungkan ketiganya) ---
echo "<h2>Menambahkan Data Nilai...</h2>";
$nilai_dani_jarkom = 'A';
$tahun_ajaran_nilai = '2024/2025';
$semester_nilai = 'Ganjil';

$check_nilai = $conn->query("SELECT id_nilai FROM nilai WHERE nim = '$nim_dani' AND kode_mk = '$kode_mk_jarkom'");
if ($check_nilai->num_rows == 0) {
    $sql_nilai = "INSERT INTO nilai (nim, kode_mk, nilai, tahun_ajaran, semester) VALUES ('$nim_dani', '$kode_mk_jarkom', '$nilai_dani_jarkom', '$tahun_ajaran_nilai', '$semester_nilai')";
    execute_sql($conn, $sql_nilai, "Nilai Dani A Hermawan untuk Jaringan Komputer berhasil ditambahkan.", "Gagal menambah nilai Dani A Hermawan untuk Jaringan Komputer");
} else {
    echo "<p style='color: orange;'>⚠️ Nilai Dani A Hermawan untuk Jaringan Komputer sudah ada, melewati penambahan.</p>";
}


echo "<h2>Proses Seeding Selesai.</h2>";

$conn->close(); // Menutup koneksi database
?>