<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }
require '../includes/db.php';
include '../includes/header.php';

if (!isset($_GET['id'])) { header('Location: index.php'); exit; }
$id = intval($_GET['id']);
$q = mysqli_query($conn, "SELECT * FROM villas WHERE id=$id");
if (!($villa = mysqli_fetch_assoc($q))) { header('Location: index.php'); exit; }

// Prepare facility data
$savedFacilities = [];
if (!empty($villa['facilities_json'])) {
    $savedFacilities = json_decode($villa['facilities_json'], true);
} else {
    // backward compat: parse comma list
    $parts = array_map('trim', explode(',', $villa['facilities']));
    foreach($parts as $p) if($p!='') $savedFacilities[] = ['key'=>null,'label'=>$p,'icon'=>null];
}

function labelFor($arr, $key, $default){
    foreach($arr as $a){ if(isset($a['key']) && $a['key']==$key) return $a['label']; }
    return $default;
}

?>

<div class="container admin-container">
    <h2>Edit Villa</h2>
    <form action="process.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $villa['id']; ?>">

        <div class="form-group">
            <label>Nama Villa</label>
            <input type="text" name="name" required value="<?php echo htmlspecialchars($villa['name']); ?>">
        </div>

        <div class="form-group">
            <label>Harga per Malam (Angka saja)</label>
            <input type="number" name="price" required value="<?php echo intval($villa['price']); ?>">
        </div>

        <div class="form-group">
            <label>Lokasi (Singkat)</label>
            <input type="text" name="location" placeholder="Contoh: Dekat Jatimpark 2" required value="<?php echo htmlspecialchars($villa['location']); ?>">
        </div>

        <div style="display:flex; gap: 10px;">
            <div class="form-group" style="flex:1">
                <label>Jml Kamar Tidur</label>
                <input type="number" name="bedroom" required value="<?php echo intval($villa['bedroom']); ?>">
            </div>
            <div class="form-group" style="flex:1">
                <label>Jml Kamar Mandi</label>
                <input type="number" name="bathroom" required value="<?php echo intval($villa['bathroom']); ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Fasilitas</label>
            <p style="margin-top:4px; color:#64748B;">Pilih ikon fasilitas lalu beri label di sampingnya.</p>

            <div id="facility-grid" style="display:grid; grid-template-columns: repeat(3, 1fr); gap:8px; margin-top:10px;">
                <?php
                $facilityOptions = [
                    ['key'=>'private_pool','icon'=>'ri-water-flash-line','label'=>'Private Pool'],
                    ['key'=>'living_room','icon'=>'ri-home-2-line','label'=>'Ruang Tamu'],
                    ['key'=>'karaoke','icon'=>'ri-mic-line','label'=>'Karaoke'],
                    ['key'=>'wifi','icon'=>'ri-wifi-line','label'=>'WiFi'],
                    ['key'=>'balcony','icon'=>'ri-building-4-line','label'=>'Balkon'],
                    ['key'=>'kitchen','icon'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width:20px;height:20px;color:#0f172a;" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 3v6a2 2 0 0 0 2 2h1v6" stroke-linecap="round" stroke-linejoin="round"></path><path d="M9 13V3" stroke-linecap="round" stroke-linejoin="round"></path><path d="M14 7v6a2 2 0 0 0 2 2h1v4" stroke-linecap="round" stroke-linejoin="round"></path><path d="M14 3v4" stroke-linecap="round" stroke-linejoin="round"></path></svg>','label'=>'Alat Dapur Lengkap'],
                    ['key'=>'bbq','icon'=>'ri-fire-line','label'=>'Alat BBQ'],
                    ['key'=>'mini_cafe','icon'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width:20px;height:20px;color:#0f172a;" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 8h14v6a3 3 0 0 1-3 3H9a3 3 0 0 1-3-3V8z" stroke-linecap="round" stroke-linejoin="round"></path><path d="M7 4v4" stroke-linecap="round" stroke-linejoin="round"></path><path d="M20 8v2" stroke-linecap="round" stroke-linejoin="round"></path></svg>','label'=>'Mini Cafe BBQ'],
                    ['key'=>'jacuzzi','icon'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width:20px;height:20px;color:#0f172a;" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2C8 6 6 8.5 6 11a6 6 0 0 0 12 0c0-2.5-2-5-6-9z" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8 18c1.5-1 3-1 5 0" stroke-linecap="round" stroke-linejoin="round"></path></svg>','label'=>'Jacuzzi'],
                    ['key'=>'garage','icon'=>'ri-car-line','label'=>'Garasi Mobil'],
                    ['key'=>'rooftop_3','icon'=>'ri-building-line','label'=>'Rooftop Lantai 3'],
                    ['key'=>'billiard','icon'=>'ri-gamepad-line','label'=>'Billiard'],
                    ['key'=>'rooftop_view','icon'=>'ri-landscape-line','label'=>'Rooftop View Istimewa'],
                    ['key'=>'capacity_8_10','icon'=>'ri-group-line','label'=>'Kapasitas 8 - 10 Orang'],
                    ['key'=>'bedroom','icon'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width:20px;height:20px;color:#0f172a;" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="7" width="18" height="10" rx="2" stroke-linecap="round" stroke-linejoin="round"></rect><path d="M7 7v-2a1 1 0 0 1 1-1h2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M7 12h10" stroke-linecap="round" stroke-linejoin="round"></path></svg>','label'=>'Kamar Tidur'],
                    ['key'=>'bathroom','icon'=>'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="width:20px;height:20px;color:#0f172a;" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2a4 4 0 0 1 4 4v3" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8 21v-6a4 4 0 0 1 8 0v6" stroke-linecap="round" stroke-linejoin="round"></path></svg>','label'=>'Kamar Mandi'],
                ];

                foreach($facilityOptions as $f){
                    $checked = '';
                    $lbl = labelFor($savedFacilities, $f['key'], $f['label']);
                    foreach($savedFacilities as $sf){ if(isset($sf['key']) && $sf['key']==$f['key']) { $checked = 'checked'; break; } }
                ?>
                    <label style="background:#fff; border:1px solid #e6e9ee; padding:8px; border-radius:8px; display:flex; gap:8px; align-items:center;">
                        <input type="checkbox" name="facility_check[]" value="<?php echo $f['key']; ?>" style="width:18px; height:18px;" <?php echo $checked; ?> >
                        <?php if (strpos(trim($f['icon']), '<svg') === 0) { echo $f['icon']; } else { echo '<i class="'.htmlspecialchars($f['icon']).'" style="font-size:20px; color:#0f172a;"></i>'; } ?>
                        <input type="text" name="facility_label_<?php echo $f['key']; ?>" value="<?php echo htmlspecialchars($lbl); ?>" style="border:none; background:transparent; font-weight:600; flex:1;">
                    </label>
                <?php } ?>
            </div>

            <input type="hidden" name="facilities_json" id="facilities_json">
        </div>

        <div class="form-group">
            <label>Deskripsi Lengkap</label>
            <textarea name="description" rows="5" required><?php echo htmlspecialchars($villa['description']); ?></textarea>
        </div>

        <div class="form-group">
            <label>Foto Utama (biarkan kosong jika tidak ingin mengganti)</label>
            <input type="file" name="image">
        </div>

        <button type="submit" name="edit_villa" class="btn-primary" style="width:100%">Simpan Perubahan</button>
    </form>
</div>

<script>
document.querySelector('form').addEventListener('submit', function(e){
    var checks = document.querySelectorAll('input[name="facility_check[]"]:checked');
    var arr = [];
        checks.forEach(function(c){
            var key = c.value;
            var inp = document.querySelector('input[name="facility_label_' + key + '"]');
            var label = inp ? inp.value : '';
            arr.push({key:key,label:label});
    });
    document.getElementById('facilities_json').value = JSON.stringify(arr);
});
</script>

<?php
include '../includes/footer.php';
?>
