<?php
// Tentukan path CSS berdasarkan lokasi file
$path_level = (basename(dirname($_SERVER['PHP_SELF'])) == 'admin') ? '../' : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VillaBatu Exclusive</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $path_level; ?>assets/css/style.css">
    
    <style>
        .admin-container { padding-top: 100px; max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .alert { padding: 10px; background: #d1fae5; color: #065f46; border-radius: 8px; margin-bottom: 20px; }
        .btn-danger { background: #ef4444; color: white; padding: 5px 10px; border-radius: 5px; font-size: 0.8rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
    </style>
</head>
<body>

<nav class="navbar scrolled">
    <div class="container nav-container">
        <a href="<?php echo $path_level; ?>index.php" class="logo">Villa<span>Batu</span>.</a>
        <ul class="nav-links">
            <li><a href="<?php echo $path_level; ?>index.php">Beranda</a></li>
            <?php if(strpos($_SERVER['REQUEST_URI'], 'admin') !== false): ?>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="add.php">Tambah Villa</a></li>
                <li><a href="process.php?action=logout">Logout</a></li>
            <?php else: ?>
                <li><a href="#villas">Daftar Villa</a></li>
                <li><a href="admin/login.php">Login Admin</a></li>
            <?php endif; ?>
        </ul>
        <div class="menu-toggle"><i class="ri-menu-4-line"></i></div>
    </div>
</nav>
