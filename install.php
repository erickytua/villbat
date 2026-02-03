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
    facilities TEXT NULL,
    facilities_json TEXT NULL,
    bedroom INT(11) NOT NULL,
    bathroom INT(11) NOT NULL,
    image VARCHAR(255) NOT NULL DEFAULT 'default.jpg',
    display_order INT NOT NULL DEFAULT 0,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    meta_keywords TEXT NULL,
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

// 5. Seed sample data jika tabel villas kosong
$resCount = mysqli_query($conn, "SELECT COUNT(*) AS c FROM villas");
$countRow = mysqli_fetch_assoc($resCount);
if (intval($countRow['c']) === 0) {
    $fac_json = '[{"key":"wifi","label":"WiFi"},{"key":"private_pool","label":"Private Pool"},{"key":"bbq","label":"Alat BBQ"}]';
    $name = mysqli_real_escape_string($conn, 'Villa Contoh');
    $location = mysqli_real_escape_string($conn, 'Kota Batu');
    $description = mysqli_real_escape_string($conn, 'Contoh deskripsi villa. Nyaman untuk keluarga.');
    $facilities = mysqli_real_escape_string($conn, 'WiFi, Private Pool, Alat BBQ');
    $fac_json_esc = mysqli_real_escape_string($conn, $fac_json);
    $meta_title = mysqli_real_escape_string($conn, 'Villa Contoh - Batu');
    $meta_description = mysqli_real_escape_string($conn, 'Deskripsi singkat Villa Contoh');

    $sample = "INSERT INTO villas (name, price, location, description, facilities, facilities_json, bedroom, bathroom, image, display_order, meta_title, meta_description) VALUES ('$name', 750000, '$location', '$description', '$facilities', '$fac_json_esc', 3, 2, 'default.jpg', 1, '$meta_title', '$meta_description')";

    if (mysqli_query($conn, $sample)) {
        echo "✅ Data contoh ditambahkan ke tabel 'villas'.<br>";
    } else {
        echo "❌ Gagal menambahkan data contoh: " . mysqli_error($conn) . "<br>";
    }
}

echo "<hr><h3>🎉 Instalasi Selesai!</h3> Silakan hapus file install.php demi keamanan, lalu buka <a href='admin/index.php'>Halaman Admin</a>.";
?>
