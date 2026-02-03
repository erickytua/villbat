<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }
require '../includes/db.php';
include '../includes/header.php';

$id = intval($_GET['id']);
$villa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM villas WHERE id=$id"));
$images = mysqli_query($conn, "SELECT * FROM villa_gallery WHERE villa_id=$id");

if(!$villa) { die("Villa tidak ditemukan"); }
?>

<div class="container admin-container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2>Galeri: <?php echo $villa['name']; ?></h2>
        <a href="index.php" class="btn-secondary">Kembali</a>
    </div>

    <div style="background:white; padding:20px; border-radius:10px; box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-bottom:30px;">
        <h4>Tambah Foto Baru</h4>
        <form action="process.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="villa_id" value="<?php echo $id; ?>">
            <div class="form-group">
                <input type="file" name="images[]" multiple required accept="image/*">
                <small style="display:block; margin-top:5px; color:#666;">Bisa pilih banyak foto sekaligus (Ctrl + Click)</small>
            </div>
            <button type="submit" name="upload_gallery" class="btn-primary">Upload Foto</button>
        </form>
    </div>

    <div class="villa-grid" style="grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));">
        <?php while($img = mysqli_fetch_assoc($images)): ?>
        <div style="position:relative;">
            <img src="../assets/uploads/<?php echo $img['image']; ?>" style="width:100%; height:150px; object-fit:cover; border-radius:10px;">
            <a href="process.php?action=delete_image&id=<?php echo $img['id']; ?>" 
               onclick="return confirm('Hapus foto ini?')"
               style="position:absolute; top:5px; right:5px; background:red; color:white; width:25px; height:25px; border-radius:50%; display:flex; align-items:center; justify-content:center; text-decoration:none;">
               &times;
            </a>
        </div>
        <?php endwhile; ?>
    </div>
</div>
