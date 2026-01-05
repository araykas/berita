<?php
require_once '../config/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email    = trim($_POST['email'] ?? '');

    if ($username === '' || $password === '' || $email === '') {
        $error = 'Semua bidang harus diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } else {
        try {
            $db = getConnection();

            $stmt = $db->prepare(
                "INSERT INTO users (username, password, email, role, is_active)
                 VALUES (:username, :password, :email, 'user', 1)"
            );
            $stmt->execute([
                ':username' => $username,
                ':password' => password_hash($password, PASSWORD_BCRYPT),
                ':email'    => $email
            ]);

            header('Location: login.php');
            exit;
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE')) {
                $error = 'Username atau email sudah terdaftar.';
            } else {
                $error = 'Terjadi kesalahan pada server.';
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>register</title>
</head>
<body>
    <h1>Register</h1>
    <form method="post">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>
        
        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>
        
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>
        
        <button type="submit">Register</button><br>
        <p><a href="login.php">Sudah punya akun? Login di sini</a></p>

        <?php if ($error): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </form>
</body>
</html>