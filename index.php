<?php
require 'includes/db.php';
include 'includes/header.php';

$query = "SELECT * FROM villas ORDER BY display_order ASC, id DESC";
$result = mysqli_query($conn, $query);
if ($result === false) {
    // Jika server MySQL versi lama atau kolom belum ada, fallback ke ordering by id
    $query = "SELECT * FROM villas ORDER BY id DESC";
    $result = mysqli_query($conn, $query);
}
?>

<header id="home" class="hero">
    <div class="container hero-content">
        <h1>Temukan Villa Terbaik di Kota Batu</h1>
        <p>Liburan impian mulai dari sini.</p>
        <a href="#villas" class="btn-primary">Lihat Villa</a>
    </div>
</header>

<section id="villas" class="section">
    <div class="container">
        <div class="section-header"><h2>Pilihan Villa Populer</h2></div>

        <div class="villa-grid">
            <?php if(mysqli_num_rows($result) > 0): ?>
                
                <?php while($villa = mysqli_fetch_assoc($result)): ?>
                <article class="villa-card scroll-reveal">
                    <div class="card-image">
                        <span class="price-tag">Rp <?php echo number_format($villa['price']); ?><span>/malam</span></span>
                        <?php 
                            $imgSrc = (strpos($villa['image'], 'http') !== false) 
                                    ? $villa['image'] 
                                    : 'assets/uploads/' . $villa['image'];
                        ?>
                        <img src="<?php echo $imgSrc; ?>" alt="<?php echo $villa['name']; ?>" loading="lazy">
                        <div class="overlay">
                            <a href="detail.php?id=<?php echo $villa['id']; ?>" class="btn-view">Lihat Detail</a>
                        </div>
                    </div>
                    <div class="card-content">
                        <h3><?php echo $villa['name']; ?></h3>
                        <p class="location"><i class="ri-map-pin-line"></i> <?php echo $villa['location']; ?></p>
                        <div class="features">
                            <span><i class="ri-hotel-bed-line"></i> <?php echo $villa['bedroom']; ?> KT</span>
                            <span><i class="ri-drop-line"></i> <?php echo $villa['bathroom']; ?> KM</span>
                        </div>
                        <hr>
                        <div class="card-action">
                            <a href="detail.php?id=<?php echo $villa['id']; ?>" class="btn-whatsapp">Lihat Detail</a>
                        </div>
                    </div>
                </article>
                <?php endwhile; ?>

            <?php else: ?>
                <p style="text-align:center; col-span:3;">Belum ada data villa.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
