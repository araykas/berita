<?php
require_once '../config/config.php';

$db = getConnection();
$error = '';
$success = '';

/*
|--------------------------------------------------------------------------
| MODE 1: REQUEST RESET (KIRIM TOKEN)
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {

    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email tidak valid.';
    } else {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Jangan bocorin apakah email ada atau tidak
        if ($user) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600);

            // hapus token lama
            $db->prepare("DELETE FROM password_resets WHERE user_id = :id")
               ->execute([':id' => $user['id']]);

            // simpan token baru (HASHED)
            $stmt = $db->prepare(
                "INSERT INTO password_resets (user_id, token, expires_at)
                 VALUES (:uid, :token, :exp)"
            );
            $stmt->execute([
                ':uid'   => $user['id'],
                ':token' => password_hash($token, PASSWORD_DEFAULT),
                ':exp'   => $expires
            ]);

            // SIMULASI EMAIL
            $link = "http://localhost:8000/auth/lupa_password.php?token=$token";
            $success = "Link reset (DEV MODE): <a href='$link'>$link</a>";
        } else {
            $success = 'Jika email terdaftar, link reset akan dikirim.';
        }
    }
}

/*
|--------------------------------------------------------------------------
| MODE 2: RESET PASSWORD
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'], $_POST['password'])) {

    $token    = $_POST['token'];
    $password = $_POST['password'];

    if (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } else {
        $stmt = $db->prepare(
            "SELECT * FROM password_resets WHERE expires_at > CURRENT_TIMESTAMP"
        );
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $reset = null;
        foreach ($rows as $row) {
            if (password_verify($token, $row['token'])) {
                $reset = $row;
                break;
            }
        }

        if (!$reset) {
            $error = 'Token tidak valid atau sudah kadaluarsa.';
        } else {
            $stmt = $db->prepare(
                "UPDATE users SET password = :pw WHERE id = :id"
            );
            $stmt->execute([
                ':pw' => password_hash($password, PASSWORD_BCRYPT),
                ':id' => $reset['user_id']
            ]);

            $db->prepare("DELETE FROM password_resets WHERE user_id = :id")
               ->execute([':id' => $reset['user_id']]);

            $resetSuccess = true;
            $success = 'Password berhasil direset.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password</title>
</head>
<body>

<h2>Lupa Password</h2>

<?php if ($error): ?>
<p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($success): ?>
<p style="color:green"><?= $success ?></p>
<?php endif; ?>

<?php if (!isset($_GET['token'])): ?>
<!-- FORM REQUEST EMAIL -->
<form method="post">
    <label>Email</label><br>
    <input type="email" name="email" required><br><br>
    <button type="submit">Kirim Link Reset</button>
</form>

<?php else: ?>
<!-- FORM RESET PASSWORD -->
<form method="post">
    <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
    <label>Password Baru</label><br>
    <input type="password" name="password" required><br><br>
    <button type="submit">Reset Password</button>
    
    <?php if (!empty($resetSuccess)): ?>
        <p>
            <a href="login.php">➡️ Ke halaman login</a>
        </p>
    <?php endif; ?>

</form>
<?php endif; ?>

</body>
</html>
