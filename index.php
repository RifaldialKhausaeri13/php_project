<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Akademik Mahasiswa</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f7f6; }
        .container { max-width: 800px; margin: auto; background: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #2c3e50; margin-bottom: 25px; }
        p { text-align: center; color: #555; font-size: 1.1em; }
        ul { list-style: none; padding: 0; text-align: center; }
        li { margin-bottom: 15px; }
        a {
            text-decoration: none;
            color: #3498db;
            font-weight: bold;
            font-size: 1.2em;
            padding: 10px 20px;
            border: 2px solid #3498db;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
            display: inline-block;
            min-width: 250px; /* Lebar minimum untuk tombol */
        }
        a:hover {
            background-color: #3498db;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sistem Akademik Mahasiswa</h1>
        <p>Selamat datang di Sistem Akademik Mahasiswa Institut Teknologi Garut. Silakan pilih menu di bawah ini untuk mengelola data:</p>
        <ul>
            <li><a href="mahasiswa.php">Manajemen Mahasiswa</a></li>
            <li><a href="dosen.php">Manajemen Dosen</a></li>
            <li><a href="matakuliah.php">Manajemen Mata Kuliah</a></li>
            <li><a href="nilai.php">Manajemen Nilai dan IPK</a></li>
            <li><a href="report.php">Laporan (Mahasiswa per Dosen/Matkul)</a></li>
        </ul>
    </div>
</body>
</html>