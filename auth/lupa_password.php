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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Aplikasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-orange-500 to-red-600 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">
    <!-- Card -->
    <div class="bg-white rounded-lg shadow-2xl p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-orange-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-800">Lupa Password?</h1>
            <p class="text-gray-500 text-sm mt-2">Kami siap membantu Anda pulihkan akun</p>
        </div>

        <!-- Error Alert -->
        <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3 animate-pulse">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <span class="text-red-700 text-sm"><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <!-- Success Alert -->
        <?php if ($success): ?>
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start gap-3">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <span class="text-green-700 text-sm"><?= $success ?></span>
        </div>
        <?php endif; ?>

        <?php if (!isset($_GET['token'])): ?>
        <!-- MODE 1: REQUEST RESET -->
        <form method="post" id="emailForm" class="space-y-5">
            <!-- Email Input -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Alamat Email
                </label>
                <input 
                    type="email" 
                    id="email"
                    name="email" 
                    required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none"
                    placeholder="Masukkan email terdaftar Anda"
                >
                <p class="text-xs text-gray-500 mt-2">
                    Kami akan mengirimkan link reset password ke email Anda.
                </p>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white font-semibold py-2.5 rounded-lg transition-all duration-200 transform hover:scale-105 active:scale-95 flex items-center justify-center gap-2"
                id="submitBtn"
            >
                <span id="btnText">Kirim Link Reset</span>
                <span id="loadingSpinner" class="hidden animate-spin">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </span>
            </button>
        </form>

        <?php else: ?>
        <!-- MODE 2: RESET PASSWORD -->
        <form method="post" id="resetForm" class="space-y-5">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
            
            <!-- New Password Input -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Password Baru
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        id="password"
                        name="password" 
                        required
                        minlength="8"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none"
                        placeholder="Minimal 8 karakter"
                        onchange="validateNewPassword(this.value)"
                    >
                    <button 
                        type="button" 
                        onclick="togglePasswordReset()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                    >
                        <svg id="eyeIconReset" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
                <p id="passwordErrorReset" class="text-xs text-red-500 mt-1 hidden">Password minimal 8 karakter</p>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white font-semibold py-2.5 rounded-lg transition-all duration-200 transform hover:scale-105 active:scale-95 flex items-center justify-center gap-2"
                id="submitBtnReset"
            >
                <span id="btnTextReset">Reset Password</span>
                <span id="loadingSpinnerReset" class="hidden animate-spin">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </span>
            </button>
            
            <?php if (!empty($resetSuccess)): ?>
            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-center">
                <p class="text-blue-700 text-sm font-semibold mb-3">✅ Password berhasil direset!</p>
                <a href="login.php" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                    Kembali ke Login
                </a>
            </div>
            <?php endif; ?>
        </form>
        <?php endif; ?>

        <!-- Back to Login -->
        <div class="mt-6 text-center">
            <a href="login.php" class="text-gray-600 hover:text-gray-800 text-sm font-medium transition-colors">
                ← Kembali ke halaman masuk
            </a>
        </div>
    </div>

    <!-- Footer -->
    <p class="text-center text-white text-xs mt-8 opacity-75">
        © 2026 Aplikasi. Semua hak dilindungi.
    </p>
</div>

<script>
function togglePasswordReset() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIconReset');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-4.803m5.596-3.856a3.375 3.375 0 11-4.753-4.753m7.538 12.016A11.966 11.966 0 0112 23c-4.478 0-8.268-2.943-9.543-7a10.025 10.025 0 01-4.132-5.411m15.946 2.389a10.05 10.05 0 011.564 4.803c-1.274 4.057-5.064 7-9.542 7-1.772 0-3.464-.38-5.048-1.045m10.262-4.058a10.025 10.025 0 0015.946-2.39"></path>';
    } else {
        passwordInput.type = 'password';
        eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
    }
}

function validateNewPassword(value) {
    const errorMsg = document.getElementById('passwordErrorReset');
    if (value.length < 8) {
        errorMsg.classList.remove('hidden');
    } else {
        errorMsg.classList.add('hidden');
    }
}

const emailForm = document.getElementById('emailForm');
if (emailForm) {
    emailForm.addEventListener('submit', function() {
        document.getElementById('btnText').classList.add('hidden');
        document.getElementById('loadingSpinner').classList.remove('hidden');
        document.getElementById('submitBtn').disabled = true;
    });
}

const resetForm = document.getElementById('resetForm');
if (resetForm) {
    resetForm.addEventListener('submit', function() {
        document.getElementById('btnTextReset').classList.add('hidden');
        document.getElementById('loadingSpinnerReset').classList.remove('hidden');
        document.getElementById('submitBtnReset').disabled = true;
    });
}
</script>

</body>
</html>
