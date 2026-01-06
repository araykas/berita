<?php
require_once '../config/config.php';

$db = getConnection();
$error = '';
$success = '';
$showResetLink = false;
$resetSuccess = false;

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
            $success = $link; // Simpan link untuk ditampilkan nanti
            $showResetLink = true;
        } else {
            $success = 'Jika email terdaftar, link reset akan dikirim.';
            $showResetLink = false;
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
<body class="bg-gradient-to-br from-orange-500 to-red-600 min-h-screen flex items-center justify-center p-3 sm:p-4 md:p-6">

<div class="w-full max-w-xs sm:max-w-sm md:max-w-md lg:max-w-lg">
    <!-- Card -->
    <div class="bg-white rounded-lg sm:rounded-xl shadow-lg sm:shadow-2xl p-5 sm:p-6 md:p-8">
        <!-- Header -->
        <div class="text-center mb-6 sm:mb-8">
            <div class="inline-flex items-center justify-center w-14 sm:w-16 h-14 sm:h-16 bg-orange-100 rounded-full mb-3 sm:mb-4">
                <svg class="w-7 sm:w-8 h-7 sm:h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
            </div>
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-800">Lupa Password?</h1>
            <p class="text-gray-500 text-xs sm:text-sm mt-1 sm:mt-2">Kami siap membantu Anda pulihkan akun</p>
        </div>

        <!-- Error Alert -->
        <?php if ($error): ?>
        <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-2 sm:gap-3 animate-pulse">
            <svg class="w-4 sm:w-5 h-4 sm:h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <span class="text-red-700 text-xs sm:text-sm"><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <!-- Success Alert -->
        <?php if ($success): ?>
        <div class="mb-4 sm:mb-6">
            <?php if (isset($showResetLink) && $showResetLink): ?>
            <!-- Professional Reset Link Display -->
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-lg p-4 sm:p-6">
                <!-- Success Icon & Message -->
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 sm:w-6 h-5 sm:h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-green-700">Link Reset Dikirim!</h3>
                        <p class="text-xs sm:text-sm text-green-600">Link berlaku selama 1 jam</p>
                    </div>
                </div>

                <!-- Reset Link Box -->
                <div class="bg-white rounded-lg border border-green-200 p-3 sm:p-4 mb-4">
                    <p class="text-xs text-gray-600 mb-2 font-medium">LINK RESET PASSWORD (MODE DEVELOPMENT):</p>
                    <div class="flex items-center gap-2 bg-gray-50 rounded-lg p-2 sm:p-3 border border-gray-200">
                        <input 
                            type="text" 
                            id="resetLink"
                            value="<?= htmlspecialchars($success) ?>" 
                            readonly
                            class="flex-1 bg-transparent outline-none text-xs sm:text-sm text-gray-700 font-mono overflow-auto"
                        >
                        <button 
                            onclick="copyToClipboard()"
                            class="px-3 sm:px-4 py-1.5 sm:py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors flex items-center gap-1 text-xs sm:text-sm font-semibold whitespace-nowrap"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Copy
                        </button>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4 mb-4">
                    <p class="text-xs sm:text-sm text-blue-800 font-medium mb-2">
                        <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd"></path>
                        </svg>
                        Cara Menggunakan:
                    </p>
                    <ol class="text-xs sm:text-sm text-blue-700 space-y-1 pl-6 list-decimal">
                        <li>Klik tombol "<strong>Copy</strong>" untuk menyalin link</li>
                        <li>Buka link di browser Anda</li>
                        <li>Masukkan password baru Anda</li>
                        <li>Klik tombol reset untuk menyelesaikan</li>
                    </ol>
                </div>

                <!-- Direct Link Button -->
                <a 
                    href="<?= htmlspecialchars($success) ?>" 
                    target="_blank"
                    class="w-full block text-center px-4 py-2.5 sm:py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-semibold rounded-lg transition-all duration-200 transform hover:scale-105 active:scale-95"
                >
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    Buka Link Reset Password
                </a>

                <!-- Important Info -->
                <div class="mt-4 pt-4 border-t border-green-200">
                    <p class="text-xs text-gray-600 text-center">
                        <svg class="w-4 h-4 inline mr-1 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        Link hanya berlaku selama 1 jam. Jangan bagikan link ini ke siapapun.
                    </p>
                </div>
            </div>
            <?php else: ?>
            <!-- Generic Success Message -->
            <div class="p-3 sm:p-4 bg-green-50 border border-green-200 rounded-lg flex items-start gap-2 sm:gap-3">
                <svg class="w-4 sm:w-5 h-4 sm:h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-green-700 text-xs sm:text-sm"><?= htmlspecialchars($success) ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!isset($_GET['token']) && (!isset($showResetLink) || !$showResetLink)): ?>
        <!-- MODE 1: REQUEST RESET -->
        <form method="post" id="emailForm" class="space-y-4 sm:space-y-5">
            <!-- Email Input -->
            <div>
                <label for="email" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">
                    Alamat Email
                </label>
                <input 
                    type="email" 
                    id="email"
                    name="email" 
                    required
                    class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none"
                    placeholder="Masukkan email terdaftar Anda"
                >
                <p class="text-xs text-gray-500 mt-1 sm:mt-2">
                    Kami akan mengirimkan link reset password ke email Anda.
                </p>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white text-sm sm:text-base font-semibold py-2 sm:py-2.5 rounded-lg transition-all duration-200 transform hover:scale-105 active:scale-95 flex items-center justify-center gap-2"
                id="submitBtn"
            >
                <span id="btnText">Kirim Link Reset</span>
                <span id="loadingSpinner" class="hidden animate-spin">
                    <svg class="w-4 sm:w-5 h-4 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </span>
            </button>
        </form>
        <?php elseif (isset($_GET['token'])): ?>
        <!-- MODE 2: RESET PASSWORD -->
        <?php if (empty($resetSuccess)): ?>
        <form method="post" id="resetForm" class="space-y-4 sm:space-y-5">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
            
            <!-- New Password Input -->
            <div>
                <label for="password" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">
                    Password Baru
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        id="password"
                        name="password" 
                        required
                        minlength="8"
                        class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all outline-none"
                        placeholder="Minimal 8 karakter"
                        onchange="validateNewPassword(this.value)"
                    >
                    <button 
                        type="button" 
                        onclick="togglePasswordReset()"
                        class="absolute right-2 sm:right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                    >
                        <svg id="eyeIconReset" class="w-4 sm:w-5 h-4 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                class="w-full bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white text-sm sm:text-base font-semibold py-2 sm:py-2.5 rounded-lg transition-all duration-200 transform hover:scale-105 active:scale-95 flex items-center justify-center gap-2"
                id="submitBtnReset"
            >
                <span id="btnTextReset">Reset Password</span>
                <span id="loadingSpinnerReset" class="hidden animate-spin">
                    <svg class="w-4 sm:w-5 h-4 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </span>
            </button>
        </form>
        <?php else: ?>
        <!-- SUCCESS MESSAGE - HIDE FORM -->
        <div class="p-4 sm:p-6 bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-lg text-center">
            <div class="flex justify-center mb-4">
                <div class="w-16 sm:w-20 h-16 sm:h-20 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 sm:w-10 h-8 sm:h-10 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
            <h2 class="text-xl sm:text-2xl font-bold text-green-700 mb-2">✅ Berhasil!</h2>
            <p class="text-green-600 text-sm sm:text-base mb-6">Password Anda telah berhasil direset.</p>
            <p class="text-gray-600 text-xs sm:text-sm mb-6">Silakan gunakan password baru Anda untuk login ke akun Anda.</p>
            <a href="login.php" class="inline-block bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-2.5 sm:py-3 px-6 sm:px-8 rounded-lg transition-all duration-200 transform hover:scale-105 active:scale-95">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h6a3 3 0 013 3v1"></path>
                </svg>
                Masuk Sekarang
            </a>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <p class="text-center text-white text-xs mt-6 sm:mt-8 opacity-75">
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

function copyToClipboard() {
    const link = document.getElementById('resetLink');
    link.select();
    document.execCommand('copy');
    
    // Show feedback
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>Tersalin!';
    btn.classList.add('bg-green-600');
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.classList.remove('bg-green-600');
    }, 2000);
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