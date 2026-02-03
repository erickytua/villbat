<?php
// Konfigurasi Database
$host = "localhost";
$user = "root";
$pass = "mb4hmarl3y!Q"; // Sesuaikan password MySQL kamu

// 1. Koneksi ke Server MySQL
$conn = mysqli_connect($host, $user, $pass);
if (!$conn) { die("Koneksi Server Gagal: " . mysqli_connect_error()); }

// 2. Buat Database
$sql_db = "CREATE DATABASE IF NOT EXISTS villabatu_db";
if (mysqli_query($conn, $sql_db)) {
    echo "✅ Database 'villabatu_db' siap.<br>";
} else {
    echo "❌ Error Database: " . mysqli_error($conn) . "<br>";
}

// Pilih Database
mysqli_select_db($conn, "villabatu_db");

// 3. Buat Tabel Utama 'villas'
$sql_villas = "CREATE TABLE IF NOT EXISTS villas (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price INT(11) NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    facilities TEXT NOT NULL,
    bedroom INT(11) NOT NULL,
    bathroom INT(11) NOT NULL,
    image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql_villas)) {
    echo "✅ Tabel 'villas' siap.<br>";
} else {
    echo "❌ Error Tabel Villas: " . mysqli_error($conn) . "<br>";
}

// 4. Buat Tabel Galeri 'villa_gallery' (BARU)
// Menggunakan ON DELETE CASCADE agar jika villa dihapus, data galeri di DB juga hilang
$sql_gallery = "CREATE TABLE IF NOT EXISTS villa_gallery (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    villa_id INT(11) NOT NULL,
    image VARCHAR(255) NOT NULL,
    FOREIGN KEY (villa_id) REFERENCES villas(id) ON DELETE CASCADE
)";

if (mysqli_query($conn, $sql_gallery)) {
    echo "✅ Tabel 'villa_gallery' siap.<br>";
} else {
    echo "❌ Error Tabel Gallery: " . mysqli_error($conn) . "<br>";
}

echo "<hr><h3>🎉 Instalasi Selesai!</h3> Silakan hapus file install.php demi keamanan, lalu buka <a href='admin/index.php'>Halaman Admin</a>.";
?>
