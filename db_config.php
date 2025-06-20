<?php
$servername = "localhost"; // Biasanya localhost jika Anda menjalankan di komputer sendiri
$username = "root";      // Username database Anda (misal: 'root' untuk XAMPP/WAMP default)
$password = "";          // Password database Anda (misal: kosong '' untuk XAMPP/WAMP default)
$dbname = "sistem_akademik"; // Nama database yang kita buat di Langkah 1

// Membuat koneksi ke database
$conn = new mysqli($servername, $username, $password, $dbname);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
// echo "Koneksi berhasil!"; // Anda bisa mengaktifkan baris ini untuk uji coba koneksi
?> 