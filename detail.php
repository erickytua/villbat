<?php
require 'includes/db.php';
include 'includes/header.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { header("Location: index.php"); exit; }

$villa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM villas WHERE id = $id"));
if (!$villa) { echo "<div class='container' style='padding-top:100px'>Villa tidak ditemukan</div>"; exit; }

// Ambil foto galeri
$galleryQ = mysqli_query($conn, "SELECT image FROM villa_gallery WHERE villa_id = $id");
$galleryImages = [];
while($row = mysqli_fetch_assoc($galleryQ)) {
    $galleryImages[] = $row['image'];
}

// Tambahkan foto utama ke urutan pertama array
array_unshift($galleryImages, $villa['image']);
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<style>
    /* Custom Styles untuk Slider */
    .swiper { width: 100%; height: 450px; border-radius: 20px; }
    .swiper-slide img { width: 100%; height: 100%; object-fit: cover; }
    .swiper-button-next, .swiper-button-prev { color: white; text-shadow: 0 2px 5px rgba(0,0,0,0.5); }
    @media(max-width: 768px) { .swiper { height: 300px; } }
</style>

<main class="container" style="padding-top: 100px;">
    <div class="page-header">
        <h1><?php echo $villa['name']; ?></h1>
        <div class="villa-meta">
            <span><i class="ri-map-pin-line"></i> <?php echo $villa['location']; ?></span>
        </div>
    </div>

    <div class="gallery-section" style="margin-bottom: 3rem;">
        <div class="swiper mySwiper">
            <div class="swiper-wrapper">
                <?php foreach($galleryImages as $img): 
                    $src = (strpos($img, 'http') !== false) ? $img : 'assets/uploads/' . $img;
                ?>
                <div class="swiper-slide">
                    <img src="<?php echo $src; ?>" alt="Villa Image">
                </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>
    </div>

    <div class="villa-layout">
        <div class="villa-details">
            <section class="description">
                <h2>Deskripsi</h2>
                <p><?php echo nl2br($villa['description']); ?></p>
            </section>
            
            <section class="facilities-section">
                <h2>Fasilitas</h2>
                <div class="facilities-grid">
                    <?php 
                    $facilities = explode(',', $villa['facilities']);
                    foreach($facilities as $f): 
                    ?>
                        <div class="facility-item"><i class="ri-checkbox-circle-line"></i> <?php echo trim($f); ?></div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <aside class="villa-sidebar">
            <div class="booking-card" id="booking">
                <div class="price-header">
                    <span class="price-amount">Rp <?php echo number_format($villa['price']); ?></span>
                    <span class="price-period">/malam</span>
                </div>
                <a href="https://wa.me/6281234567890?text=Saya%20mau%20pesan%20<?php echo urlencode($villa['name']); ?>" class="btn-whatsapp-lg" target="_blank">
                    <i class="ri-whatsapp-fill"></i> Pesan via WhatsApp
                </a>
            </div>
        </aside>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    var swiper = new Swiper(".mySwiper", {
        loop: true,
        spaceBetween: 10,
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
    });
</script>

<?php include 'includes/footer.php'; ?>
