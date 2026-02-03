<?php
require 'includes/db.php';

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

// Prepare SEO meta for header
$meta_title = $villa['name'] . ' - Villa di ' . $villa['location'];
$meta_description = substr(strip_tags($villa['description']), 0, 160);
$meta_keywords = implode(', ', array_map('trim', explode(',', $villa['facilities'])));
$canonical = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

include 'includes/header.php';
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
                        <div class="swiper-zoom-container">
                            <img src="<?php echo $src; ?>" alt="<?php echo htmlspecialchars($villa['name']); ?>">
                        </div>
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
                    $iconMap = [
                        'private_pool'=>'ri-water-flash-line','living_room'=>'ri-home-2-line','karaoke'=>'ri-mic-line',
                        'wifi'=>'ri-wifi-line','balcony'=>'ri-building-4-line','kitchen'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width:20px;height:20px;color:currentColor;" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 3v6a2 2 0 0 0 2 2h1v6" stroke-linecap="round" stroke-linejoin="round"></path><path d="M9 13V3" stroke-linecap="round" stroke-linejoin="round"></path><path d="M14 7v6a2 2 0 0 0 2 2h1v4" stroke-linecap="round" stroke-linejoin="round"></path><path d="M14 3v4" stroke-linecap="round" stroke-linejoin="round"></path></svg>','bbq'=>'ri-fire-line',
                        'mini_cafe'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width:20px;height:20px;color:currentColor;" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 8h14v6a3 3 0 0 1-3 3H9a3 3 0 0 1-3-3V8z" stroke-linecap="round" stroke-linejoin="round"></path><path d="M7 4v4" stroke-linecap="round" stroke-linejoin="round"></path><path d="M20 8v2" stroke-linecap="round" stroke-linejoin="round"></path></svg>','jacuzzi'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width:20px;height:20px;color:currentColor;" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2C8 6 6 8.5 6 11a6 6 0 0 0 12 0c0-2.5-2-5-6-9z" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8 18c1.5-1 3-1 5 0" stroke-linecap="round" stroke-linejoin="round"></path></svg>','garage'=>'ri-car-line','rooftop_3'=>'ri-building-line',
                        'billiard'=>'ri-gamepad-line','rooftop_view'=>'ri-landscape-line','capacity_8_10'=>'ri-group-line','bedroom'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width:20px;height:20px;color:currentColor;" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="7" width="18" height="10" rx="2" stroke-linecap="round" stroke-linejoin="round"></rect><path d="M7 7v-2a1 1 0 0 1 1-1h2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M7 12h10" stroke-linecap="round" stroke-linejoin="round"></path></svg>','bathroom'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width:20px;height:20px;color:currentColor;" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2a4 4 0 0 1 4 4v3" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8 21v-6a4 4 0 0 1 8 0v6" stroke-linecap="round" stroke-linejoin="round"></path></svg>'
                    ];

                    if (!empty($villa['facilities_json'])) {
                        $fac = json_decode($villa['facilities_json'], true);
                        if (is_array($fac)){
                            foreach($fac as $f){
                                $k = isset($f['key']) ? $f['key'] : null;
                                $lbl = isset($f['label']) ? $f['label'] : '';
                                $ic = ($k && isset($iconMap[$k])) ? $iconMap[$k] : 'ri-checkbox-circle-line';
                                if (strpos(trim($ic), '<svg') === 0) {
                                    echo '<div class="facility-item">' . $ic . ' ' . htmlspecialchars($lbl) . '</div>';
                                } else {
                                    echo '<div class="facility-item"><i class="'.$ic.'"></i> '.htmlspecialchars($lbl).'</div>';
                                }
                            }
                        }
                    } else {
                        $facilities = explode(',', $villa['facilities']);
                        foreach($facilities as $f){
                            echo '<div class="facility-item"><i class="ri-checkbox-circle-line"></i> '.htmlspecialchars(trim($f)).'</div>';
                        }
                    }
                    ?>
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

<!-- Mobile sticky booking bar -->
<div class="mobile-booking-bar">
    <div class="price-info">
        <div class="price">Rp <?php echo number_format($villa['price']); ?></div>
        <div class="label">/malam</div>
    </div>
    <a href="https://wa.me/6281234567890?text=Saya%20mau%20pesan%20<?php echo urlencode($villa['name']); ?>" class="btn-book-mobile">Pesan</a>
</div>

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
        zoom: {
            maxRatio: 3,
            toggle: true,
        },
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
    });

    // Make slides tappable to allow user to control slide vs zoom
    document.querySelectorAll('.swiper-slide .swiper-zoom-container img').forEach(function(img){
        img.style.cursor = 'zoom-in';
    });
</script>

<?php include 'includes/footer.php'; ?>
