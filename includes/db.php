<?php
$host = "localhost";
$user = "root";
$pass = "mb4hmarl3y!Q"; // Sesuaikan dengan password Ubuntu tadi
$db   = "villabatu_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    // Jika errornya karena database belum ada, jangan die dulu (biar install.php jalan)
    if (mysqli_connect_errno() != 1049) { 
        die("Koneksi Gagal: " . mysqli_connect_error());
    }
}
?>
