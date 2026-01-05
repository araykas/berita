<?php
session_start();
require_once '../config/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password harus diisi.';
    } else {
        try {
            $db = getConnection();
            $stmt = $db->prepare(
                "SELECT * FROM users WHERE username = :username LIMIT 1"
            );
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $error = 'Username atau password salah.';
            } elseif (!$user['is_active']) {
                $error = 'Akun tidak aktif.';
            } elseif ($user['banned_until'] && strtotime($user['banned_until']) > time()) {
                $error = 'Akun sedang diblokir sementara.';
            } elseif (!password_verify($password, $user['password'])) {
                $error = 'Username atau password salah.';
            } else {
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header('Location: ../admin/dashboard.php');
                } else {
                    header('Location: ../public/index.php');
                }
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan pada server.';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>

<h2>Login</h2>

<?php if ($error): ?>
<p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post">
    <label>Username</label><br>
    <input type="text" name="username"><br><br>

    <label>Password</label><br>
    <input type="password" name="password"><br><br>

    <button type="submit">Login</button>
    <button type="button" onclick="window.location.href='register.php'">Register</button><br><br>

    <p><a href="lupa_password.php">Lupa Password</a></p>
</form>

</body>
</html>
