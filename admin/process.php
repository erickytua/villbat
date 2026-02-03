<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }

$targetDir = "../assets/uploads/";

// LOGOUT
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: login.php");
    exit;
}

// === DELETE VILLA (TERMASUK SEMUA GAMBAR) ===
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // 1. Hapus Foto Utama
    $qMain = mysqli_query($conn, "SELECT image FROM villas WHERE id=$id");
    if($row = mysqli_fetch_assoc($qMain)){
        $path = $targetDir . $row['image'];
        if(file_exists($path) && $row['image'] != 'default.jpg') unlink($path);
    }

    // 2. Hapus Semua Foto Galeri Fisik
    $qGallery = mysqli_query($conn, "SELECT image FROM villa_gallery WHERE villa_id=$id");
    while($row = mysqli_fetch_assoc($qGallery)){
        $path = $targetDir . $row['image'];
        if(file_exists($path)) unlink($path);
    }

    // 3. Hapus Data di Database (Cascade akan menghapus data di villa_gallery juga, tapi kita hapus manual biar aman di beberapa versi MySQL)
    mysqli_query($conn, "DELETE FROM villa_gallery WHERE villa_id = $id");
    mysqli_query($conn, "DELETE FROM villas WHERE id = $id");

    header("Location: index.php");
    exit;
}

// === DELETE SATU FOTO GALERI ===
if (isset($_GET['action']) && $_GET['action'] == 'delete_image' && isset($_GET['id'])) {
    $imgId = intval($_GET['id']);
    
    // Ambil info file & villa_id untuk redirect balik
    $q = mysqli_query($conn, "SELECT image, villa_id FROM villa_gallery WHERE id=$imgId");
    if($row = mysqli_fetch_assoc($q)){
        $path = $targetDir . $row['image'];
        if(file_exists($path)) unlink($path); // Hapus fisik
        
        mysqli_query($conn, "DELETE FROM villa_gallery WHERE id=$imgId"); // Hapus DB
        
        header("Location: gallery.php?id=" . $row['villa_id']); // Balik ke halaman galeri
    } else {
        header("Location: index.php");
    }
    exit;
}

// === UPLOAD FOTO GALERI BARU ===
if (isset($_POST['upload_gallery'])) {
    $villa_id = intval($_POST['villa_id']);
    
    // Loop multiple files
    $count = count($_FILES['images']['name']);
    
    for($i=0; $i<$count; $i++) {
        if($_FILES['images']['error'][$i] == 0) {
            $fileName = time() . '_' . $i . '_' . basename($_FILES['images']['name'][$i]); // Unik name
            $targetFilePath = $targetDir . $fileName;
            
            if(move_uploaded_file($_FILES['images']['tmp_name'][$i], $targetFilePath)){
                // Simpan ke DB
                mysqli_query($conn, "INSERT INTO villa_gallery (villa_id, image) VALUES ('$villa_id', '$fileName')");
            }
        }
    }
    header("Location: gallery.php?id=$villa_id");
    exit;
}

// === ADD NEW VILLA (SAMA SEPERTI SEBELUMNYA) ===
if (isset($_POST['add_villa'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = intval($_POST['price']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $facilities = mysqli_real_escape_string($conn, $_POST['facilities']);
    $bedroom = intval($_POST['bedroom']);
    $bathroom = intval($_POST['bathroom']);

    $imageName = "default.jpg";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $fileName = time() . '_' . basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            $imageName = $fileName;
        }
    }

    $sql = "INSERT INTO villas (name, price, location, description, facilities, bedroom, bathroom, image) 
            VALUES ('$name', '$price', '$location', '$description', '$facilities', '$bedroom', '$bathroom', '$imageName')";

    if(mysqli_query($conn, $sql)){
        header("Location: index.php?status=success");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    exit;
}
?>
