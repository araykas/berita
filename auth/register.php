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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Aplikasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-green-500 to-teal-600 min-h-screen flex items-center justify-center p-3 sm:p-4 md:p-6">

<div class="w-full max-w-xs sm:max-w-sm md:max-w-md lg:max-w-lg">
    <!-- Card -->
    <div class="bg-white rounded-lg sm:rounded-xl shadow-lg sm:shadow-2xl p-5 sm:p-6 md:p-8">
        <!-- Header -->
        <div class="text-center mb-6 sm:mb-8">
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-800">Daftar Akun</h1>
            <p class="text-gray-500 text-xs sm:text-sm mt-1 sm:mt-2">Bergabunglah dengan komunitas kami</p>
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

        <!-- Form -->
        <form method="post" id="registerForm" class="space-y-4 sm:space-y-5">
            <!-- Username Input -->
            <div>
                <label for="username" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">
                    Username
                </label>
                <input 
                    type="text" 
                    id="username"
                    name="username" 
                    required
                    minlength="3"
                    class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all outline-none"
                    placeholder="Pilih username Anda"
                    onchange="validateUsername(this.value)"
                >
                <p id="usernameError" class="text-xs text-red-500 mt-1 hidden">Username minimal 3 karakter</p>
            </div>

            <!-- Email Input -->
            <div>
                <label for="email" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">
                    Email
                </label>
                <input 
                    type="email" 
                    id="email"
                    name="email" 
                    required
                    class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all outline-none"
                    placeholder="nama@example.com"
                >
            </div>

            <!-- Password Input -->
            <div>
                <label for="password" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">
                    Password
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        id="password"
                        name="password" 
                        required
                        minlength="8"
                        class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all outline-none"
                        placeholder="Minimal 8 karakter"
                        onchange="validatePassword(this.value)"
                    >
                    <button 
                        type="button" 
                        onclick="togglePassword2()"
                        class="absolute right-2 sm:right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                    >
                        <svg id="eyeIcon2" class="w-4 sm:w-5 h-4 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
                <p id="passwordError" class="text-xs text-red-500 mt-1 hidden">Password minimal 8 karakter</p>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full bg-gradient-to-r from-green-500 to-teal-600 hover:from-green-600 hover:to-teal-700 text-white text-sm sm:text-base font-semibold py-2 sm:py-2.5 rounded-lg transition-all duration-200 transform hover:scale-105 active:scale-95 flex items-center justify-center gap-2"
                id="submitBtn"
            >
                <span id="btnText">Daftar</span>
                <span id="loadingSpinner" class="hidden animate-spin">
                    <svg class="w-4 sm:w-5 h-4 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </span>
            </button>
        </form>

        <!-- Divider -->
        <div class="mt-4 sm:mt-6 flex items-center gap-3 sm:gap-4">
            <div class="flex-1 h-px bg-gray-200"></div>
            <span class="text-gray-500 text-xs sm:text-sm">atau</span>
            <div class="flex-1 h-px bg-gray-200"></div>
        </div>

        <!-- Login Link -->
        <div class="mt-4 sm:mt-6 text-center">
            <p class="text-gray-600 text-xs sm:text-sm">
                Sudah punya akun?
                <a href="login.php" class="text-green-500 hover:text-green-700 font-semibold transition-colors">
                    Masuk di sini
                </a>
            </p>
        </div>
    </div>

    <!-- Footer -->
    <p class="text-center text-white text-xs mt-6 sm:mt-8 opacity-75">
        © 2026 Aplikasi. Semua hak dilindungi.
    </p>
</div>

<script>
function togglePassword2() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon2');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-4.803m5.596-3.856a3.375 3.375 0 11-4.753-4.753m7.538 12.016A11.966 11.966 0 0112 23c-4.478 0-8.268-2.943-9.543-7a10.025 10.025 0 01-4.132-5.411m15.946 2.389a10.05 10.05 0 011.564 4.803c-1.274 4.057-5.064 7-9.542 7-1.772 0-3.464-.38-5.048-1.045m10.262-4.058a10.025 10.025 0 0015.946-2.39"></path>';
    } else {
        passwordInput.type = 'password';
        eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
    }
}

function validateUsername(value) {
    const errorMsg = document.getElementById('usernameError');
    if (value.length < 3) {
        errorMsg.classList.remove('hidden');
    } else {
        errorMsg.classList.add('hidden');
    }
}

function validatePassword(value) {
    const errorMsg = document.getElementById('passwordError');
    if (value.length < 8) {
        errorMsg.classList.remove('hidden');
    } else {
        errorMsg.classList.add('hidden');
    }
}

document.getElementById('registerForm').addEventListener('submit', function() {
    document.getElementById('btnText').classList.add('hidden');
    document.getElementById('loadingSpinner').classList.remove('hidden');
    document.getElementById('submitBtn').disabled = true;
});
</script>

</body>
</html>