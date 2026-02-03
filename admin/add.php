<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }
include '../includes/header.php';
?>

<div class="container admin-container">
    <h2>Tambah Villa Baru</h2>
    <form action="process.php" method="POST" enctype="multipart/form-data">
        
        <div class="form-group">
            <label>Nama Villa</label>
            <input type="text" name="name" required>
        </div>

        <div class="form-group">
            <label>Harga per Malam (Angka saja)</label>
            <input type="number" name="price" required>
        </div>

        <div class="form-group">
            <label>Lokasi (Singkat)</label>
            <input type="text" name="location" placeholder="Contoh: Dekat Jatimpark 2" required>
        </div>

        <div style="display:flex; gap: 10px;">
            <div class="form-group" style="flex:1">
                <label>Jml Kamar Tidur</label>
                <input type="number" name="bedroom" required>
            </div>
            <div class="form-group" style="flex:1">
                <label>Jml Kamar Mandi</label>
                <input type="number" name="bathroom" required>
            </div>
        </div>

        <div class="form-group">
            <label>Fasilitas (Pisahkan dengan koma)</label>
            <input type="text" name="facilities" placeholder="WiFi, Kolam Renang, Karaoke, BBQ">
        </div>

        <div class="form-group">
            <label>Deskripsi Lengkap</label>
            <textarea name="description" rows="5" required></textarea>
        </div>

        <div class="form-group">
            <label>Foto Utama</label>
            <input type="file" name="image" required>
        </div>

        <button type="submit" name="add_villa" class="btn-primary" style="width:100%">Simpan Villa</button>
    </form>
</div>
