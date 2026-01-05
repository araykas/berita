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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aplikasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-500 to-purple-600 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">
    <!-- Card -->
    <div class="bg-white rounded-lg shadow-2xl p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Masuk</h1>
            <p class="text-gray-500 text-sm mt-2">Selamat datang kembali</p>
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

        <!-- Form -->
        <form method="post" id="loginForm" class="space-y-5">
            <!-- Username Input -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                    Username
                </label>
                <input 
                    type="text" 
                    id="username"
                    name="username" 
                    required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all outline-none"
                    placeholder="Masukkan username Anda"
                >
            </div>

            <!-- Password Input -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Password
                    </label>
                    <a href="lupa_password.php" class="text-xs text-blue-500 hover:text-blue-700 transition-colors">
                        Lupa password?
                    </a>
                </div>
                <div class="relative">
                    <input 
                        type="password" 
                        id="password"
                        name="password" 
                        required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all outline-none"
                        placeholder="Masukkan password Anda"
                    >
                    <button 
                        type="button" 
                        onclick="togglePassword()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700"
                    >
                        <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold py-2.5 rounded-lg transition-all duration-200 transform hover:scale-105 active:scale-95 flex items-center justify-center gap-2"
                id="submitBtn"
            >
                <span id="btnText">Masuk</span>
                <span id="loadingSpinner" class="hidden animate-spin">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </span>
            </button>
        </form>

        <!-- Divider -->
        <div class="mt-6 flex items-center gap-4">
            <div class="flex-1 h-px bg-gray-200"></div>
            <span class="text-gray-500 text-sm">atau</span>
            <div class="flex-1 h-px bg-gray-200"></div>
        </div>

        <!-- Register Link -->
        <div class="mt-6 text-center">
            <p class="text-gray-600 text-sm">
                Belum punya akun?
                <a href="register.php" class="text-blue-500 hover:text-blue-700 font-semibold transition-colors">
                    Daftar di sini
                </a>
            </p>
        </div>
    </div>

    <!-- Footer -->
    <p class="text-center text-white text-xs mt-8 opacity-75">
        © 2026 Aplikasi. Semua hak dilindungi.
    </p>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-4.803m5.596-3.856a3.375 3.375 0 11-4.753-4.753m7.538 12.016A11.966 11.966 0 0112 23c-4.478 0-8.268-2.943-9.543-7a10.025 10.025 0 01-4.132-5.411m15.946 2.389a10.05 10.05 0 011.564 4.803c-1.274 4.057-5.064 7-9.542 7-1.772 0-3.464-.38-5.048-1.045m10.262-4.058a10.025 10.025 0 0015.946-2.39"></path>';
    } else {
        passwordInput.type = 'password';
        eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
    }
}

document.getElementById('loginForm').addEventListener('submit', function() {
    document.getElementById('btnText').classList.add('hidden');
    document.getElementById('loadingSpinner').classList.remove('hidden');
    document.getElementById('submitBtn').disabled = true;
});
</script>

</body>
</html>
