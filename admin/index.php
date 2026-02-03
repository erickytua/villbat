<?php
session_start();
// Cek sesi login
if (!isset($_SESSION['admin_logged_in'])) { 
    header("Location: login.php"); 
    exit; 
}

require '../includes/db.php';
include '../includes/header.php';

// Ambil semua data villa urut dari yang terbaru
$query = "SELECT * FROM villas ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<div class="container admin-container">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
        <div>
            <h2 style="margin-bottom: 5px;">Dashboard Admin</h2>
            <p style="color: #64748B;">Kelola data villa dan galeri foto.</p>
        </div>
        <a href="add.php" class="btn-primary"><i class="ri-add-line"></i> Tambah Villa Baru</a>
    </div>

    <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div class="alert">Data berhasil disimpan!</div>
    <?php endif; ?>

    <div style="overflow-x: auto; background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <table>
            <thead>
                <tr>
                    <th style="width: 120px;">Gambar Utama</th>
                    <th>Nama Villa & Lokasi</th>
                    <th>Harga / Malam</th>
                    <th style="width: 200px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($villa = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <?php 
                                $img = (strpos($villa['image'], 'http') !== false) 
                                        ? $villa['image'] 
                                        : '../assets/uploads/'.$villa['image'];
                            ?>
                            <img src="<?php echo $img; ?>" style="width: 100px; height: 60px; object-fit: cover; border-radius: 6px;">
                        </td>
                        <td>
                            <strong><?php echo $villa['name']; ?></strong><br>
                            <small style="color: #64748B;"><i class="ri-map-pin-line"></i> <?php echo $villa['location']; ?></small>
                        </td>
                        <td>Rp <?php echo number_format($villa['price']); ?></td>
                        <td style="text-align: center;">
                            <a href="gallery.php?id=<?php echo $villa['id']; ?>" 
                               style="background: #10B981; color: white; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; text-decoration: none; margin-right: 5px; display: inline-block;">
                               <i class="ri-image-edit-line"></i> Galeri
                            </a>
                            
                            <a href="process.php?action=delete&id=<?php echo $villa['id']; ?>" 
                               class="btn-danger" 
                               onclick="return confirm('Yakin hapus? Semua foto galeri villa ini juga akan terhapus permanen.');"
                               style="background: #EF4444; color: white; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; text-decoration: none; display: inline-block;">
                               <i class="ri-delete-bin-line"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding: 30px; color: #64748B;">
                            Belum ada data villa. Silakan klik tombol "Tambah Villa Baru".
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    table { width: 100%; border-collapse: collapse; }
    thead { background-color: #F1F5F9; border-bottom: 2px solid #E2E8F0; }
    th { text-align: left; padding: 15px; font-weight: 600; color: #1E293B; }
    td { padding: 15px; border-bottom: 1px solid #E2E8F0; vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tr:hover { background-color: #F8FAFC; }
    .alert { background: #D1FAE5; color: #065F46; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #10B981; }
</style>
