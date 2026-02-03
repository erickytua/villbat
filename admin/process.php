<?php
session_start();
require '../includes/db.php';

if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }

$targetDir = "../assets/uploads/";

// small helper to check column existence (works even if ALTER TABLE with IF NOT EXISTS isn't supported)
function column_exists($conn, $table, $column){
    $t = mysqli_real_escape_string($conn, $table);
    $c = mysqli_real_escape_string($conn, $column);
    $res = mysqli_query($conn, "SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = '$t' AND column_name = '$c' LIMIT 1");
    if(!$res) return false;
    return (mysqli_fetch_row($res) !== null);
}

// Try best-effort to add columns on newer MySQL, but don't rely on it (suppressed errors)
@mysqli_query($conn, "ALTER TABLE villas ADD COLUMN IF NOT EXISTS facilities_json TEXT NULL, ADD COLUMN IF NOT EXISTS display_order INT DEFAULT 0");

// Ensure `display_order` exists and is populated (fallback for older MySQL)
if (!column_exists($conn, 'villas', 'display_order')) {
    @mysqli_query($conn, "ALTER TABLE villas ADD COLUMN display_order INT DEFAULT 0");
    if (column_exists($conn, 'villas', 'display_order')) {
        $res = mysqli_query($conn, "SELECT id FROM villas ORDER BY id ASC");
        if ($res) {
            $i = 1;
            while($r = mysqli_fetch_assoc($res)){
                $vid = intval($r['id']);
                mysqli_query($conn, "UPDATE villas SET display_order=$i WHERE id=$vid");
                $i++;
            }
        }
    }
}

// LOGOUT
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: login.php");
    exit;
}

// === EDIT VILLA ===
if (isset($_POST['edit_villa'])) {
    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = intval($_POST['price']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $facilities = isset($_POST['facilities']) ? mysqli_real_escape_string($conn, $_POST['facilities']) : '';
    $facilities_json = isset($_POST['facilities_json']) ? mysqli_real_escape_string($conn, $_POST['facilities_json']) : '';
    $bedroom = isset($_POST['bedroom']) ? intval($_POST['bedroom']) : 0;
    $bathroom = isset($_POST['bathroom']) ? intval($_POST['bathroom']) : 0;

    // Handle image replacement
    $newImage = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // remove old
        $qold = mysqli_query($conn, "SELECT image FROM villas WHERE id=$id");
        if($row = mysqli_fetch_assoc($qold)){
            $old = $row['image'];
            $path = $targetDir . $old;
            if(file_exists($path) && $old != 'default.jpg') unlink($path);
        }
        $fileName = time() . '_' . basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        if(move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)){
            $newImage = $fileName;
        }
    }
    // Build update dynamically depending on whether facilities_json column exists
    $has_fjson = column_exists($conn, 'villas', 'facilities_json');
    $fac_from_json = '';
    if (!$has_fjson && !empty($facilities_json)) {
        $fj = json_decode($facilities_json, true);
        if (is_array($fj)) {
            $labels = [];
            foreach($fj as $f){
                if (is_array($f) && isset($f['label'])) $labels[] = $f['label'];
                elseif (is_string($f)) $labels[] = $f;
            }
            if (!empty($labels)) $fac_from_json = mysqli_real_escape_string($conn, implode(', ', $labels));
        }
    }

    $fields = [];
    $fields[] = "name='$name'";
    $fields[] = "price='$price'";
    $fields[] = "location='$location'";
    $fields[] = "description='$description'";
    if ($has_fjson) {
        $fields[] = "facilities='$facilities'";
        $fields[] = "facilities_json='$facilities_json'";
    } else {
        $useFac = ($fac_from_json !== '') ? $fac_from_json : $facilities;
        $fields[] = "facilities='$useFac'";
    }
    $fields[] = "bedroom='$bedroom'";
    $fields[] = "bathroom='$bathroom'";
    if ($newImage !== '') $fields[] = "image='$newImage'";

    $sql = "UPDATE villas SET " . implode(', ', $fields) . " WHERE id=$id";
    mysqli_query($conn, $sql);
    header("Location: index.php?status=success");
    exit;
}

// === MOVE ORDER UP/DOWN ===
if (isset($_GET['action']) && isset($_GET['id']) && in_array($_GET['action'], ['move_up','move_down'])) {
    $id = intval($_GET['id']);
    $q = mysqli_query($conn, "SELECT id, display_order FROM villas WHERE id=$id");
    if($row = mysqli_fetch_assoc($q)){
        $cur = intval($row['display_order']);
        if($_GET['action'] == 'move_up'){
            $q2 = mysqli_query($conn, "SELECT id, display_order FROM villas WHERE display_order < $cur ORDER BY display_order DESC LIMIT 1");
        } else {
            $q2 = mysqli_query($conn, "SELECT id, display_order FROM villas WHERE display_order > $cur ORDER BY display_order ASC LIMIT 1");
        }
        if($row2 = mysqli_fetch_assoc($q2)){
            // swap
            $otherId = intval($row2['id']);
            $otherOrder = intval($row2['display_order']);
            mysqli_query($conn, "UPDATE villas SET display_order=$otherOrder WHERE id=$id");
            mysqli_query($conn, "UPDATE villas SET display_order=$cur WHERE id=$otherId");
        }
    }
    header("Location: index.php");
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
    $facilities = isset($_POST['facilities']) ? mysqli_real_escape_string($conn, $_POST['facilities']) : '';
    $facilities_json = isset($_POST['facilities_json']) ? mysqli_real_escape_string($conn, $_POST['facilities_json']) : '';
    $bedroom = intval($_POST['bedroom']);
    $bathroom = intval($_POST['bathroom']);

    // Optional SEO fields
    $meta_title = isset($_POST['meta_title']) ? mysqli_real_escape_string($conn, $_POST['meta_title']) : '';
    $meta_description = isset($_POST['meta_description']) ? mysqli_real_escape_string($conn, $_POST['meta_description']) : '';
    $meta_keywords = isset($_POST['meta_keywords']) ? mysqli_real_escape_string($conn, $_POST['meta_keywords']) : '';

    $imageName = "default.jpg";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $fileName = time() . '_' . basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            $imageName = $fileName;
        }
    }

        // Determine next display order
        $qmax = mysqli_query($conn, "SELECT MAX(display_order) AS m FROM villas");
        $mrow = mysqli_fetch_assoc($qmax);
        $nextOrder = ($mrow && $mrow['m']) ? intval($mrow['m']) + 1 : 1;

        // Build INSERT dynamically depending on column availability
        $has_fjson = column_exists($conn, 'villas', 'facilities_json');
        $fac_from_json = '';
        if (!$has_fjson && !empty($facilities_json)) {
            $fj = json_decode($facilities_json, true);
            if (is_array($fj)) {
                $labels = [];
                foreach($fj as $f){
                    if (is_array($f) && isset($f['label'])) $labels[] = $f['label'];
                    elseif (is_string($f)) $labels[] = $f;
                }
                if (!empty($labels)) $fac_from_json = mysqli_real_escape_string($conn, implode(', ', $labels));
            }
        }

        $cols = ['name','price','location','description','bedroom','bathroom','image','display_order'];
        $vals = ["'$name'","'$price'","'$location'","'$description'","'$bedroom'","'$bathroom'","'$imageName'", $nextOrder];
        if ($has_fjson) {
            array_splice($cols, 4, 0, ['facilities','facilities_json']);
            array_splice($vals, 4, 0, ["'$facilities'", "'$facilities_json'"]);
        } else {
            $useFac = ($fac_from_json !== '') ? $fac_from_json : $facilities;
            array_splice($cols, 4, 0, ['facilities']);
            array_splice($vals, 4, 0, ["'$useFac'"]);
        }

        $sql = "INSERT INTO villas (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";

    // Try to add SEO columns if they don't exist (MySQL 8+ supports IF NOT EXISTS)
    @mysqli_query($conn, "ALTER TABLE villas ADD COLUMN IF NOT EXISTS meta_title VARCHAR(255) NULL, ADD COLUMN IF NOT EXISTS meta_description TEXT NULL, ADD COLUMN IF NOT EXISTS meta_keywords TEXT NULL");

    // If SEO values provided, include them in INSERT (safer approach: run separate UPDATE after insert)

    if(mysqli_query($conn, $sql)){
        $newId = mysqli_insert_id($conn);
        // Save SEO fields if provided
        if (!empty($meta_title) || !empty($meta_description) || !empty($meta_keywords)) {
            $mt = mysqli_real_escape_string($conn, $meta_title);
            $md = mysqli_real_escape_string($conn, $meta_description);
            $mk = mysqli_real_escape_string($conn, $meta_keywords);
            mysqli_query($conn, "UPDATE villas SET meta_title='$mt', meta_description='$md', meta_keywords='$mk' WHERE id=$newId");
        }
        header("Location: index.php?status=success");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    exit;
}
?>
