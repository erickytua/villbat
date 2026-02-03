<?php
session_start();
if (isset($_POST['login'])) {
    // Hardcode username & password (Ganti ini nanti!)
    if ($_POST['username'] == 'admin' && $_POST['password'] == 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = "Password salah!";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Login</title><style>body{display:flex;justify-content:center;align-items:center;height:100vh;background:#f1f5f9;font-family:sans-serif;} form{background:white;padding:2rem;border-radius:10px;box-shadow:0 4px 6px rgba(0,0,0,0.1);} input{display:block;margin:10px 0;padding:10px;width:100%;} button{background:#2563EB;color:white;border:none;padding:10px;width:100%;cursor:pointer;}</style></head>
<body>
    <form method="POST">
        <h2>Admin Login</h2>
        <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>
</body>
</html>
